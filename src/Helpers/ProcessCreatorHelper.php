<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessStatusLog;
use IlBronza\Processes\Models\ProcessType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessCreatorHelper
{
    public static function create(array $attributes, array $options = []): Process
    {
        return DB::transaction(function () use ($attributes, $options) {
            $type = static::resolveType($attributes['process_type_id'] ?? $attributes['type'] ?? null);
            $status = static::resolveInitialStatus($type, $attributes['process_status_id'] ?? $attributes['status'] ?? null);

            $attributes['process_type_id'] = $type->getKey();
            $attributes['process_status_id'] = $status->getKey();

            static::validateRequirements($type, $attributes);
            static::validateOverlaps($type, $attributes);

            $process = Process::getProjectClassName()::create($attributes);

            if(config('processes.behavior.create_initial_status_log', true))
                ProcessStatusLog::getProjectClassName()::create([
                    'process_id' => $process->getKey(),
                    'process_status_id' => $status->getKey(),
                    'previous_process_status_id' => null,
                    'user_id' => static::resolveUserId($options['user'] ?? null),
                    'set_at' => $options['set_at'] ?? now(),
                    'reason' => $options['reason'] ?? null,
                    'data' => $options['data'] ?? null,
                    'notes' => $options['notes'] ?? null,
                ]);

            return $process;
        });
    }

    protected static function resolveType(ProcessType|string|null $type): ProcessType
    {
        if($type instanceof ProcessType)
            return $type;

        if(! $type)
            throw new InvalidArgumentException('A process type is required.');

        return ProcessType::getProjectClassName()::query()
            ->whereKey($type)
            ->orWhere('name', $type)
            ->firstOrFail();
    }

    protected static function resolveInitialStatus(ProcessType $type, ProcessStatus|string|null $status): ProcessStatus
    {
        if($status)
            return ProcessStatusSetterHelper::resolveStatus($status);

        if($type->defaultStatus)
            return $type->defaultStatus;

        return ProcessStatus::getProjectClassName()::query()->initial()->active()->ordered()->firstOrFail();
    }

    protected static function validateRequirements(ProcessType $type, array $attributes): void
    {
        if($type->requires_operator && ! ($attributes['operator_id'] ?? null))
            throw new InvalidArgumentException('This process type requires an operator.');

        if($type->requires_tool && ! (($attributes['toolable_type'] ?? null) && ($attributes['toolable_id'] ?? null)))
            throw new InvalidArgumentException('This process type requires a tool.');

        if($type->requires_processable && ! (($attributes['processable_type'] ?? null) && ($attributes['processable_id'] ?? null)))
            throw new InvalidArgumentException('This process type requires a related processable object.');
    }

    protected static function validateOverlaps(ProcessType $type, array $attributes): void
    {
        if(! config('processes.behavior.validate_overlaps', true))
            return;

        if($type->allows_overlaps)
            return;

        if(! (($attributes['starts_at'] ?? null) && ($attributes['ends_at'] ?? null)))
            return;

        if($operatorId = $attributes['operator_id'] ?? null)
            if(ProcessOverlapCheckerHelper::hasOperatorOverlaps($operatorId, $attributes['starts_at'], $attributes['ends_at']))
                throw new InvalidArgumentException('The selected operator has overlapping processes.');

        if(($attributes['toolable_type'] ?? null) && ($attributes['toolable_id'] ?? null))
            if(static::hasMorphOverlaps('toolable', $attributes, $attributes['starts_at'], $attributes['ends_at']))
                throw new InvalidArgumentException('The selected tool has overlapping processes.');

        if(($attributes['processable_type'] ?? null) && ($attributes['processable_id'] ?? null))
            if(static::hasMorphOverlaps('processable', $attributes, $attributes['starts_at'], $attributes['ends_at']))
                throw new InvalidArgumentException('The selected processable object has overlapping processes.');
    }

    protected static function hasMorphOverlaps(string $morph, array $attributes, mixed $startsAt, mixed $endsAt): bool
    {
        return Process::getProjectClassName()::query()
            ->where("{$morph}_type", $attributes["{$morph}_type"])
            ->where("{$morph}_id", $attributes["{$morph}_id"])
            ->overlapping($startsAt, $endsAt)
            ->exists();
    }

    protected static function resolveUserId(Model|string|null $user): ?string
    {
        if($user instanceof Model)
            return $user->getKey();

        if($user)
            return $user;

        return auth()->id();
    }
}

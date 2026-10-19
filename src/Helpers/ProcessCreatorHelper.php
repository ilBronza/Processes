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

            unset($attributes['type'], $attributes['status']);
            $locked = (bool) ($attributes['is_locked'] ?? false);
            $closedAt = $attributes['closed_at'] ?? null;
            unset($attributes['is_locked'], $attributes['locked_at'], $attributes['locked_by_user_id'],
                $attributes['closed_at'], $attributes['closed_by_user_id']);

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

            if($closedAt)
                ProcessCloserHelper::close($process, array_merge($options, ['closed_at' => $closedAt]));
            if($locked)
                ProcessLockerHelper::lock($process, $options['user'] ?? null);

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

    protected static function resolveUserId(Model|string|null $user): ?string
    {
        if($user instanceof Model)
            return $user->getKey();

        if($user)
            return $user;

        return auth()->id();
    }
}

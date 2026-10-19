<?php

namespace IlBronza\Processes\Helpers;

use Carbon\Carbon;
use IlBronza\Processes\Models\Process;
use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessStatusLog;
use IlBronza\Processes\Models\ProcessStatusTransition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use LogicException;

class ProcessStatusSetterHelper
{
    public static function setStatus(Process $process, ProcessStatus|string $status, array $options = []): ProcessStatusLog
    {
        $status = static::resolveStatus($status);

        if(! $status->is_active)
            throw new InvalidArgumentException('Inactive process statuses cannot be assigned.');

        if($process->is_locked && ! ($options['force'] ?? false))
            throw new LogicException('Locked processes cannot change status without force mode.');

        $transition = static::getTransition($process, $status);

        if(config('processes.behavior.validate_transitions', true))
            static::validateTransition($transition, $options);

        return $process->getConnection()->transaction(function () use ($process, $status, $options) {
            $previousStatusId = $process->process_status_id;
            $setAt = Carbon::parse($options['set_at'] ?? now());

            $processClass = $process::class;

            $processClass::allowingStatusUpdate(function () use ($processClass, $process, $status, $options) {
                $processClass::allowingLockedUpdate(function () use ($process, $status, $options) {
                    $process->process_status_id = $status->getKey();

                    foreach(['starts_at', 'ends_at', 'closed_at'] as $field)
                        if(array_key_exists($field, $options))
                            $process->{$field} = $options[$field];

                    $process->save();
                });
            });

            return ProcessStatusLog::getProjectClassName()::create([
                'process_id' => $process->getKey(),
                'process_status_id' => $status->getKey(),
                'previous_process_status_id' => $previousStatusId,
                'user_id' => static::resolveUserId($options['user'] ?? null),
                'set_at' => $setAt,
                'reason' => $options['reason'] ?? null,
                'data' => $options['data'] ?? null,
                'notes' => $options['notes'] ?? null,
            ]);
        });
    }

    public static function resolveStatus(ProcessStatus|string $status): ProcessStatus
    {
        if($status instanceof ProcessStatus)
            return $status;

        return ProcessStatus::getProjectClassName()::query()
            ->whereKey($status)
            ->orWhere('name', $status)
            ->firstOrFail();
    }

    protected static function getTransition(Process $process, ProcessStatus $status): ?ProcessStatusTransition
    {
        $query = ProcessStatusTransition::getProjectClassName()::query()
            ->where('to_status_id', $status->getKey())
            ->where(function ($query) use ($process) {
                $query->whereNull('process_type_id')
                    ->orWhere('process_type_id', $process->process_type_id);
            });

        if($process->process_status_id)
            $query->where('from_status_id', $process->process_status_id);
        else
            $query->whereNull('from_status_id');

        return $query->orderByRaw('process_type_id IS NULL')
            ->orderBy('sort_index')
            ->first();
    }

    protected static function validateTransition(?ProcessStatusTransition $transition, array $options): void
    {
        if(! $transition || ! $transition->is_allowed)
            throw new LogicException('This process status transition is not allowed.');

        if($transition->requires_reason && trim($options['reason'] ?? '') === '')
            throw new LogicException('This process status transition requires a reason.');

        if($permission = $transition->requires_permission)
        {
            $user = $options['user'] ?? auth()->user();

            if(! $user || Gate::forUser($user)->denies($permission))
                throw new LogicException('This process status transition requires permission: ' . $permission);
        }
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

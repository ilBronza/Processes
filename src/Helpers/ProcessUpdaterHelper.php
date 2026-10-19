<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use Illuminate\Validation\ValidationException;

class ProcessUpdaterHelper
{
    public static function update(Process $process, array $attributes, array $options = []): Process
    {
        return $process->getConnection()->transaction(function () use ($process, $attributes, $options) {
            $process = $process->newQuery()->lockForUpdate()->findOrFail($process->getKey());
            $locked = array_key_exists('is_locked', $attributes) ? (bool) $attributes['is_locked'] : $process->is_locked;
            $unlock = $process->is_locked && ! $locked;

            if($process->is_locked && $locked)
                throw ValidationException::withMessages(['is_locked' => trans('processes::validation.locked')]);

            $status = $attributes['process_status_id'] ?? $process->process_status_id;
            $hasClosure = array_key_exists('closed_at', $attributes);
            $closedAt = $attributes['closed_at'] ?? null;
            unset($attributes['process_status_id'], $attributes['is_locked'], $attributes['locked_at'],
                $attributes['locked_by_user_id'], $attributes['closed_at'], $attributes['closed_by_user_id']);

            $process->fill($attributes);
            if($unlock)
                ProcessLockerHelper::unlock($process);
            $statusChanged = $status !== $process->process_status_id;

            if($hasClosure && $closedAt && (! $process->closed_at || ! $process->closed_at->equalTo($closedAt)))
                ProcessCloserHelper::close($process, array_merge($options, [
                    'closed_at' => $closedAt,
                    'status' => $statusChanged ? $status : null,
                ]));
            else
            {
                if($hasClosure && ! $closedAt)
                {
                    $process->closed_at = null;
                    $process->closed_by_user_id = null;
                }

                if($statusChanged)
                    ProcessStatusSetterHelper::setStatus($process, $status, $options);
                else
                    $process->save();
            }

            if($locked)
                ProcessLockerHelper::lock($process, $options['user'] ?? null);

            return $process;
        });
    }
}

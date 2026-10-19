<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class ProcessCloserHelper
{
    public static function close(Process $process, array $options = []): Process
    {
        if($process->is_locked && ! ($options['force'] ?? false))
            throw new LogicException('Locked processes cannot be closed without force mode.');

        return $process->getConnection()->transaction(function () use ($process, $options) {
            $process->closed_at = $options['closed_at'] ?? now();
            $process->closed_by_user_id = static::resolveUserId($options['user'] ?? null);

            if(($options['set_ends_at'] ?? false) && ! $process->ends_at)
                $process->ends_at = $options['ends_at'] ?? $process->closed_at;

            if($status = $options['status'] ?? null)
                ProcessStatusSetterHelper::setStatus($process, $status, $options);
            else
                $process::allowingLockedUpdate(fn () => $process->save());

            return $process->refresh();
        });
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

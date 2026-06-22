<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use Illuminate\Database\Eloquent\Model;

class ProcessLockerHelper
{
    public static function lock(Process $process, Model|string|null $user = null): Process
    {
        $processClass = $process::class;

        return $processClass::allowingLockedUpdate(function () use ($process, $user) {
            $process->is_locked = true;
            $process->locked_at = now();
            $process->locked_by_user_id = static::resolveUserId($user);
            $process->save();

            return $process;
        });
    }

    public static function unlock(Process $process): Process
    {
        $processClass = $process::class;

        return $processClass::allowingLockedUpdate(function () use ($process) {
            $process->is_locked = false;
            $process->locked_at = null;
            $process->locked_by_user_id = null;
            $process->save();

            return $process;
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

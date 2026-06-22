<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use IlBronza\Processes\Models\ProcessStatus;
use Illuminate\Database\Eloquent\Model;

class ProcessCloserHelper
{
    public static function close(Process $process, array $options = []): Process
    {
        $processClass = $process::class;

        $processClass::allowingLockedUpdate(function () use ($process, $options) {
            $process->closed_at = $options['closed_at'] ?? now();
            $process->closed_by_user_id = static::resolveUserId($options['user'] ?? null);

            if(($options['set_ends_at'] ?? false) && ! $process->ends_at)
                $process->ends_at = $options['ends_at'] ?? $process->closed_at;

            $process->save();
        });

        if($status = $options['status'] ?? null)
            ProcessStatusSetterHelper::setStatus($process, $status, [
                'force' => $options['force'] ?? false,
                'user' => $options['user'] ?? null,
                'reason' => $options['reason'] ?? null,
                'closed_at' => $process->closed_at,
            ]);

        return $process->refresh();
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

<?php

namespace IlBronza\Processes\Helpers;

use Carbon\Carbon;
use IlBronza\Processes\Models\Process;

class ProcessDurationCalculatorHelper
{
    public static function fillDurations(Process $process): void
    {
        $process->planned_duration_seconds = static::calculateSeconds(
            $process->planned_starts_at,
            $process->planned_ends_at
        );

        $process->duration_seconds = static::calculateSeconds(
            $process->starts_at,
            $process->ends_at
        );
    }

    public static function calculateSeconds(mixed $startsAt, mixed $endsAt): ?int
    {
        if(! $startsAt || ! $endsAt)
            return null;

        $startsAt = Carbon::parse($startsAt);
        $endsAt = Carbon::parse($endsAt);

        if($endsAt->lessThanOrEqualTo($startsAt))
            return 0;

        return $startsAt->diffInSeconds($endsAt);
    }
}

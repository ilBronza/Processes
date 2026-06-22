<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use Illuminate\Database\Eloquent\Model;

class OperatorAvailabilityHelper
{
    public static function isAvailable(Model|string $operator, mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null, bool $blockingStatusesOnly = false): bool
    {
        $query = Process::getProjectClassName()::query()
            ->forOperator($operator)
            ->overlapping($startsAt, $endsAt);

        if($ignore)
            $query->where($query->getModel()->getKeyName(), '!=', $ignore instanceof Process ? $ignore->getKey() : $ignore);

        if($blockingStatusesOnly)
            $query->whereHas('status', fn ($query) => $query->blocking());
        else
            $query->whereHas('status', fn ($query) => $query->active());

        return ! $query->exists();
    }
}

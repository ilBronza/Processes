<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProcessOverlapCheckerHelper
{
    public static function operatorOverlaps(Model|string $operator, mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null): Collection
    {
        return static::baseOverlapQuery($startsAt, $endsAt, $ignore)
            ->forOperator($operator)
            ->get();
    }

    public static function toolOverlaps(Model $tool, mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null): Collection
    {
        return static::baseOverlapQuery($startsAt, $endsAt, $ignore)
            ->forTool($tool)
            ->get();
    }

    public static function processableOverlaps(Model $processable, mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null): Collection
    {
        return static::baseOverlapQuery($startsAt, $endsAt, $ignore)
            ->forProcessable($processable)
            ->get();
    }

    public static function hasOperatorOverlaps(Model|string $operator, mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null): bool
    {
        return static::operatorOverlaps($operator, $startsAt, $endsAt, $ignore)->isNotEmpty();
    }

    public static function hasToolOverlaps(Model $tool, mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null): bool
    {
        return static::toolOverlaps($tool, $startsAt, $endsAt, $ignore)->isNotEmpty();
    }

    public static function hasProcessableOverlaps(Model $processable, mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null): bool
    {
        return static::processableOverlaps($processable, $startsAt, $endsAt, $ignore)->isNotEmpty();
    }

    protected static function baseOverlapQuery(mixed $startsAt, mixed $endsAt, Process|string|null $ignore = null): Builder
    {
        $query = Process::getProjectClassName()::query()->overlapping($startsAt, $endsAt);

        if($ignore)
            $query->where($query->getModel()->getKeyName(), '!=', $ignore instanceof Process ? $ignore->getKey() : $ignore);

        return $query;
    }
}

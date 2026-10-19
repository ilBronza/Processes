<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessType;
use Illuminate\Validation\ValidationException;

class ProcessValidatorHelper
{
    public static function validate(Process $process): void
    {
        $type = ProcessType::getProjectClassName()::find($process->process_type_id);

        if(! $type || ((! $process->exists || $process->isDirty('process_type_id')) && ! $type->is_active))
            static::fail('process_type_id', 'active_type');

        if(! $process->exists || $process->isDirty('process_status_id'))
        {
            $status = ProcessStatus::getProjectClassName()::find($process->process_status_id);
            if(! $status || ! $status->is_active)
                static::fail('process_status_id', 'active_status');
        }

        if($type->requires_operator && ! $process->operator_id)
            static::fail('operator_id', 'operator_required');

        foreach(['toolable' => 'requires_tool', 'processable' => 'requires_processable'] as $morph => $requirement)
        {
            $hasType = (bool) $process->{"{$morph}_type"};
            $hasId = (bool) $process->{"{$morph}_id"};
            if($hasType !== $hasId || ($type->{$requirement} && ! $hasId))
                static::fail("{$morph}_id", 'related_required');
        }

        foreach(['', 'planned_'] as $prefix)
        {
            $start = $process->{$prefix . 'starts_at'};
            $end = $process->{$prefix . 'ends_at'};
            if($end && ! $start)
                static::fail($prefix . 'starts_at', 'start_required');
            if($start && $end && $end->lessThan($start))
                static::fail($prefix . 'ends_at', 'invalid_range');
        }

        if($process->parent_id && (! $process->exists || $process->isDirty('parent_id')))
        {
            $parent = Process::getProjectClassName()::find($process->parent_id);
            if(! $parent)
                static::fail('parent_id', 'invalid_parent');
            try {
                ProcessTreeBuilderHelper::ensureValidParent($process, $parent);
            } catch (\LogicException $exception) {
                static::fail('parent_id', 'invalid_parent');
            }
        }

        $scheduleFields = ['process_type_id', 'operator_id', 'toolable_type', 'toolable_id',
            'processable_type', 'processable_id', 'starts_at', 'ends_at', 'planned_starts_at', 'planned_ends_at'];

        if(config('processes.behavior.validate_overlaps', true) && ! $type->allows_overlaps
            && (! $process->exists || $process->isDirty($scheduleFields)))
            static::validateOverlaps($process);
    }

    protected static function validateOverlaps(Process $process): void
    {
        // Actual and planned schedules are checked separately. An absent end is open-ended.
        foreach(['', 'planned_'] as $prefix)
        {
            $start = $process->{$prefix . 'starts_at'};
            if(! $start)
                continue;

            $base = Process::getProjectClassName()::query()
                ->overlapping($start, $process->{$prefix . 'ends_at'}, $prefix);
            if($process->exists)
                $base->whereKeyNot($process->getKey());

            if($process->operator_id && (clone $base)->forOperator($process->operator_id)->exists())
                static::fail('operator_id', 'overlap');

            foreach(['toolable', 'processable'] as $morph)
                if($process->{"{$morph}_id"} && (clone $base)
                    ->where("{$morph}_type", $process->{"{$morph}_type"})
                    ->where("{$morph}_id", $process->{"{$morph}_id"})->exists())
                    static::fail("{$morph}_id", 'overlap');
        }
    }

    protected static function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => trans('processes::validation.' . $message)]);
    }
}

<?php

namespace IlBronza\Processes\Helpers;

use Illuminate\Support\Collection;

class ProcessTimelineBuilderHelper
{
    public static function build(iterable $processes): array
    {
        return collect($processes)->map(fn ($process) => [
            'id' => $process->getKey(),
            'title' => $process->type?->label ?? $process->type?->name ?? $process->getKey(),
            'type' => $process->type ? [
                'id' => $process->type->getKey(),
                'name' => $process->type->name,
                'label' => $process->type->label,
                'color' => $process->type->color,
                'icon' => $process->type->icon,
            ] : null,
            'status' => $process->status ? [
                'id' => $process->status->getKey(),
                'name' => $process->status->name,
                'label' => $process->status->label,
                'color' => $process->status->color,
                'icon' => $process->status->icon,
            ] : null,
            'operator_id' => $process->operator_id,
            'processable_type' => $process->processable_type,
            'processable_id' => $process->processable_id,
            'toolable_type' => $process->toolable_type,
            'toolable_id' => $process->toolable_id,
            'starts_at' => $process->starts_at,
            'ends_at' => $process->ends_at,
            'planned_starts_at' => $process->planned_starts_at,
            'planned_ends_at' => $process->planned_ends_at,
            'duration_seconds' => $process->duration_seconds,
            'planned_duration_seconds' => $process->planned_duration_seconds,
            'is_locked' => $process->is_locked,
        ])->values()->all();
    }
}

<?php

namespace IlBronza\Processes\Traits;

use IlBronza\Processes\Models\Process;

trait HasOperatorProcesses
{
    public function processes()
    {
        return $this->hasMany(Process::getProjectClassName(), 'operator_id');
    }

    public function activeProcesses()
    {
        return $this->processes()->active();
    }

    public function plannedProcesses()
    {
        return $this->processes()->whereNotNull('planned_starts_at')->whereNotNull('planned_ends_at');
    }

    public function completedProcesses()
    {
        return $this->processes()->whereHas('status', fn ($query) => $query->final());
    }

    public function processesBetween(mixed $from, mixed $to)
    {
        return $this->processes()->between($from, $to);
    }
}

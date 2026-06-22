<?php

namespace IlBronza\Processes\Traits;

use IlBronza\Processes\Models\Process;

trait InteractsWithProcesses
{
    public function processes()
    {
        return $this->morphMany(Process::getProjectClassName(), 'processable');
    }

    public function currentProcesses()
    {
        return $this->processes()->active();
    }

    public function latestProcess()
    {
        return $this->morphOne(Process::getProjectClassName(), 'processable')->latestOfMany();
    }

    public function completedProcesses()
    {
        return $this->processes()->whereHas('status', fn ($query) => $query->final());
    }

    public function activeProcesses()
    {
        return $this->currentProcesses();
    }
}

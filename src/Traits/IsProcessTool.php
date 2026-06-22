<?php

namespace IlBronza\Processes\Traits;

use IlBronza\Processes\Models\Process;

trait IsProcessTool
{
    public function toolProcesses()
    {
        return $this->morphMany(Process::getProjectClassName(), 'toolable');
    }

    public function activeToolProcesses()
    {
        return $this->toolProcesses()->active();
    }

    public function latestToolProcess()
    {
        return $this->morphOne(Process::getProjectClassName(), 'toolable')->latestOfMany();
    }
}

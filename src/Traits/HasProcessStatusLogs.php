<?php

namespace IlBronza\Processes\Traits;

use IlBronza\Processes\Helpers\ProcessStatusSetterHelper;
use IlBronza\Processes\Models\ProcessStatusLog;

trait HasProcessStatusLogs
{
    public function statusLogs()
    {
        return $this->hasMany(ProcessStatusLog::getProjectClassName(), 'process_id');
    }

    public function lastStatusLog()
    {
        return $this->hasOne(ProcessStatusLog::getProjectClassName(), 'process_id')->latestOfMany('set_at');
    }

    public function setStatus(mixed $status, array $options = []): ProcessStatusLog
    {
        return ProcessStatusSetterHelper::setStatus($this, $status, $options);
    }

    public function getLastStatusLog(): ?ProcessStatusLog
    {
        if($this->relationLoaded('lastStatusLog'))
            return $this->lastStatusLog;

        return $this->lastStatusLog()->first();
    }
}

<?php

namespace IlBronza\Processes\Models;

class ProcessStatusLog extends ProcessPackageBaseModel
{
    public static $modelConfigPrefix = 'processStatusLog';

    protected $guarded = [];

    protected $casts = [
        'set_at' => 'datetime',
        'data' => 'array',
    ];

    // The audit table has no deleted_at column. Its rows are removed by the
    // process foreign key cascade, not by the base model's SoftDeletes scope.
    public static function bootSoftDeletes()
    {
    }

    protected function performDeleteOnModel()
    {
        throw new \LogicException('Process status logs cannot be deleted directly.');
    }

    public function process()
    {
        return $this->belongsTo(Process::getProjectClassName(), 'process_id');
    }

    public function status()
    {
        return $this->belongsTo(ProcessStatus::getProjectClassName(), 'process_status_id');
    }

    public function previousStatus()
    {
        return $this->belongsTo(ProcessStatus::getProjectClassName(), 'previous_process_status_id');
    }

    public function user()
    {
        return $this->belongsTo(config('processes.models.user.class'));
    }
}

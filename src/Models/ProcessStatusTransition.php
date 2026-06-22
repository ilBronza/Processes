<?php

namespace IlBronza\Processes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessStatusTransition extends ProcessPackageBaseModel
{
    use SoftDeletes;

    public static $modelConfigPrefix = 'processStatusTransition';

    protected $guarded = [];

    protected $casts = [
        'is_allowed' => 'boolean',
        'requires_reason' => 'boolean',
        'sort_index' => 'integer',
        'data' => 'array',
        'metadata' => 'array',
    ];

    public function processType()
    {
        return $this->belongsTo(ProcessType::getProjectClassName(), 'process_type_id');
    }

    public function fromStatus()
    {
        return $this->belongsTo(ProcessStatus::getProjectClassName(), 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(ProcessStatus::getProjectClassName(), 'to_status_id');
    }

    public function scopeAllowed(Builder $query): void
    {
        $query->where('is_allowed', true);
    }
}

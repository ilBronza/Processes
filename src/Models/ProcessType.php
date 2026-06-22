<?php

namespace IlBronza\Processes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessType extends ProcessPackageBaseModel
{
    use SoftDeletes;

    public static $modelConfigPrefix = 'processType';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'allows_overlaps' => 'boolean',
        'requires_operator' => 'boolean',
        'requires_tool' => 'boolean',
        'requires_processable' => 'boolean',
        'sort_index' => 'integer',
        'data' => 'array',
        'metadata' => 'array',
    ];

    public function processes()
    {
        return $this->hasMany(Process::getProjectClassName(), 'process_type_id');
    }

    public function defaultStatus()
    {
        return $this->belongsTo(ProcessStatus::getProjectClassName(), 'default_status_id');
    }

    public function transitions()
    {
        return $this->hasMany(ProcessStatusTransition::getProjectClassName(), 'process_type_id');
    }

    public function fields()
    {
        return $this->hasMany(ProcessTypeField::getProjectClassName(), 'process_type_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_index')->orderBy('label');
    }
}

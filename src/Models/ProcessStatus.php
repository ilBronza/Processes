<?php

namespace IlBronza\Processes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessStatus extends ProcessPackageBaseModel
{
    use SoftDeletes;

    public static $modelConfigPrefix = 'processStatus';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_initial' => 'boolean',
        'is_final' => 'boolean',
        'is_positive' => 'boolean',
        'is_negative' => 'boolean',
        'is_blocking' => 'boolean',
        'sort_index' => 'integer',
        'data' => 'array',
        'metadata' => 'array',
    ];

    public function processes()
    {
        return $this->hasMany(Process::getProjectClassName(), 'process_status_id');
    }

    public function logs()
    {
        return $this->hasMany(ProcessStatusLog::getProjectClassName(), 'process_status_id');
    }

    public function outgoingTransitions()
    {
        return $this->hasMany(ProcessStatusTransition::getProjectClassName(), 'from_status_id');
    }

    public function incomingTransitions()
    {
        return $this->hasMany(ProcessStatusTransition::getProjectClassName(), 'to_status_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeInitial(Builder $query): void
    {
        $query->where('is_initial', true);
    }

    public function scopeFinal(Builder $query): void
    {
        $query->where('is_final', true);
    }

    public function scopePositive(Builder $query): void
    {
        $query->where('is_positive', true);
    }

    public function scopeNegative(Builder $query): void
    {
        $query->where('is_negative', true);
    }

    public function scopeBlocking(Builder $query): void
    {
        $query->where('is_blocking', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_index')->orderBy('label');
    }
}

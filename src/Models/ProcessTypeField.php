<?php

namespace IlBronza\Processes\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessTypeField extends ProcessPackageBaseModel
{
    use SoftDeletes;

    public static $modelConfigPrefix = 'processTypeField';

    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_index' => 'integer',
        'options' => 'array',
        'validation_rules' => 'array',
        'data' => 'array',
        'metadata' => 'array',
    ];

    public function processType()
    {
        return $this->belongsTo(ProcessType::getProjectClassName(), 'process_type_id');
    }
}

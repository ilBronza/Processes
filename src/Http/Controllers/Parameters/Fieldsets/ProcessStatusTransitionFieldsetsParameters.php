<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class ProcessStatusTransitionFieldsetsParameters extends FieldsetParametersFile
{
    public function _getFieldsetsParameters(): array
    {
        return [
            'base' => [
                'translationPrefix' => 'processes::fields',
                'fields' => [
                    'process_type_id' => ['type' => 'select', 'rules' => 'string|nullable|exists:' . config('processes.models.processType.table') . ',id', 'relation' => 'processType'],
                    'from_status_id' => ['type' => 'select', 'rules' => 'string|nullable|exists:' . config('processes.models.processStatus.table') . ',id', 'relation' => 'fromStatus'],
                    'to_status_id' => ['type' => 'select', 'rules' => 'string|required|exists:' . config('processes.models.processStatus.table') . ',id', 'relation' => 'toStatus'],
                    'is_allowed' => ['boolean' => 'boolean|nullable'],
                    'requires_reason' => ['boolean' => 'boolean|nullable'],
                    'requires_permission' => ['text' => 'string|nullable|max:255'],
                    'sort_index' => ['number' => 'integer|nullable'],
                    'data' => ['json' => 'array|nullable'],
                    'metadata' => ['json' => 'array|nullable'],
                ],
                'width' => ['1-2@l', '1-1@m'],
            ],
        ];
    }
}

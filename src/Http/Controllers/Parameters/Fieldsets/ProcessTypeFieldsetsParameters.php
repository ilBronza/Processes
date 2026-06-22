<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class ProcessTypeFieldsetsParameters extends FieldsetParametersFile
{
    public function _getFieldsetsParameters(): array
    {
        return [
            'base' => [
                'translationPrefix' => 'processes::fields',
                'fields' => [
                    'name' => ['text' => 'string|required|max:255|unique:' . config('processes.models.processType.table') . ',name'],
                    'label' => ['text' => 'string|required|max:255'],
                    'description' => ['textarea' => 'string|nullable'],
                    'default_status_id' => ['type' => 'select', 'rules' => 'string|nullable|exists:' . config('processes.models.processStatus.table') . ',id', 'relation' => 'defaultStatus'],
                    'is_active' => ['boolean' => 'boolean|nullable'],
                    'allows_overlaps' => ['boolean' => 'boolean|nullable'],
                    'requires_operator' => ['boolean' => 'boolean|nullable'],
                    'requires_tool' => ['boolean' => 'boolean|nullable'],
                    'requires_processable' => ['boolean' => 'boolean|nullable'],
                    'sort_index' => ['number' => 'integer|nullable'],
                    'color' => ['text' => 'string|nullable|max:255'],
                    'icon' => ['text' => 'string|nullable|max:255'],
                    'data' => ['json' => 'array|nullable'],
                    'metadata' => ['json' => 'array|nullable'],
                ],
                'width' => ['1-2@l', '1-1@m'],
            ],
        ];
    }
}

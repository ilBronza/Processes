<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class ProcessTypeFieldFieldsetsParameters extends FieldsetParametersFile
{
    public function _getFieldsetsParameters(): array
    {
        return [
            'base' => [
                'translationPrefix' => 'processes::fields',
                'fields' => [
                    'process_type_id' => ['type' => 'select', 'rules' => 'string|required|exists:' . config('processes.models.processType.table') . ',id', 'relation' => 'processType'],
                    'name' => ['text' => 'string|required|max:255'],
                    'label' => ['text' => 'string|required|max:255'],
                    'type' => ['text' => 'string|required|max:255'],
                    'is_required' => ['boolean' => 'boolean|nullable'],
                    'default_value' => ['textarea' => 'string|nullable'],
                    'sort_index' => ['number' => 'integer|nullable'],
                    'options' => ['json' => 'array|nullable'],
                    'validation_rules' => ['json' => 'array|nullable'],
                    'data' => ['json' => 'array|nullable'],
                    'metadata' => ['json' => 'array|nullable'],
                ],
                'width' => ['1-2@l', '1-1@m'],
            ],
        ];
    }
}

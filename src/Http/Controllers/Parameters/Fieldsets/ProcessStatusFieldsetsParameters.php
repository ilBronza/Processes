<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class ProcessStatusFieldsetsParameters extends FieldsetParametersFile
{
    public function _getFieldsetsParameters(): array
    {
        return [
            'base' => [
                'translationPrefix' => 'processes::fields',
                'fields' => [
                    'name' => ['text' => 'string|required|max:255|unique:' . config('processes.models.processStatus.table') . ',name'],
                    'label' => ['text' => 'string|required|max:255'],
                    'description' => ['textarea' => 'string|nullable'],
                    'is_active' => ['boolean' => 'boolean|nullable'],
                    'is_initial' => ['boolean' => 'boolean|nullable'],
                    'is_final' => ['boolean' => 'boolean|nullable'],
                    'is_positive' => ['boolean' => 'boolean|nullable'],
                    'is_negative' => ['boolean' => 'boolean|nullable'],
                    'is_blocking' => ['boolean' => 'boolean|nullable'],
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

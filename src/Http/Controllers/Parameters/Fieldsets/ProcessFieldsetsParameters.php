<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Fieldsets;

use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;

class ProcessFieldsetsParameters extends FieldsetParametersFile
{
    public function _getFieldsetsParameters(): array
    {
        return [
            'base' => [
                'translationPrefix' => 'processes::fields',
                'fields' => [
                    'process_type_id' => ['type' => 'select', 'rules' => 'string|required|exists:' . config('processes.models.processType.table') . ',id', 'relation' => 'type'],
                    'process_status_id' => ['type' => 'select', 'rules' => 'string|required|exists:' . config('processes.models.processStatus.table') . ',id', 'relation' => 'status'],
                    'user_id' => ['text' => 'string|nullable'],
                    'operator_id' => ['text' => 'string|nullable'],
                    'processable_type' => ['text' => 'string|nullable|max:255'],
                    'processable_id' => ['text' => 'string|nullable|max:255'],
                    'toolable_type' => ['text' => 'string|nullable|max:255'],
                    'toolable_id' => ['text' => 'string|nullable|max:255'],
                    'parent_id' => ['type' => 'select', 'rules' => 'string|nullable|exists:' . config('processes.models.process.table') . ',id', 'relation' => 'parent'],
                    'priority' => ['number' => 'integer|nullable'],
                    'sort_index' => ['number' => 'integer|nullable'],
                    'is_locked' => ['boolean' => 'boolean|nullable'],
                ],
                'width' => ['1-3@l', '1-2@m'],
            ],
            'dates' => [
                'translationPrefix' => 'processes::fields',
                'fields' => [
                    'planned_starts_at' => ['datetime' => 'date|nullable'],
                    'planned_ends_at' => ['datetime' => 'date|nullable'],
                    'starts_at' => ['datetime' => 'date|nullable'],
                    'ends_at' => ['datetime' => 'date|nullable'],
                    'closed_at' => ['datetime' => 'date|nullable'],
                ],
                'width' => ['1-3@l', '1-2@m'],
            ],
            'external' => [
                'translationPrefix' => 'processes::fields',
                'fields' => [
                    'external_source' => ['text' => 'string|nullable|max:255'],
                    'external_id' => ['text' => 'string|nullable|max:255'],
                    'notes' => ['textarea' => 'string|nullable'],
                    'data' => ['json' => 'array|nullable'],
                    'metadata' => ['json' => 'array|nullable'],
                    'external_payload' => ['json' => 'array|nullable'],
                ],
                'width' => ['1-3@l', '1-2@m'],
            ],
        ];
    }
}

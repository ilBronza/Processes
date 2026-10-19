<?php

namespace IlBronza\Processes\Helpers\Crud;

use IlBronza\CRUD\Helpers\ModelManagers\CrudModelUpdaterEditor;

class ProcessCrudEditor extends CrudModelUpdaterEditor
{
    use PersistsProcesses;

    public function getValidationParameters(): array
    {
        return array_merge(parent::getValidationParameters(), ['status_reason' => 'nullable|string']);
    }

    public function sanitizeParametersAndValues(array $parameters): array
    {
        $result = parent::sanitizeParametersAndValues($parameters);
        if(array_key_exists('status_reason', $parameters))
            $result['status_reason'] = $parameters['status_reason'];

        return $result;
    }
}

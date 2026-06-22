<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Datatables;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ProcessStatusTransitionFieldsGroupParametersFile extends FieldsGroupParametersFile
{
    public static function getFieldsGroup(): array
    {
        return [
            'translationPrefix' => 'processes::fields',
            'fields' => [
                'mySelfPrimary' => 'primary',
                'mySelfEdit' => 'links.edit',
                'mySelfSee' => 'links.see',
                'processType' => 'relations.belongsTo',
                'fromStatus' => 'relations.belongsTo',
                'toStatus' => 'relations.belongsTo',
                'is_allowed' => 'boolean',
                'requires_reason' => 'boolean',
                'requires_permission' => 'flat',
                'sort_index' => 'flat',
                'mySelfDelete' => 'links.delete',
            ],
        ];
    }
}

<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Datatables;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ProcessTypeFieldsGroupParametersFile extends FieldsGroupParametersFile
{
    public static function getFieldsGroup(): array
    {
        return [
            'translationPrefix' => 'processes::fields',
            'fields' => [
                'mySelfPrimary' => 'primary',
                'mySelfEdit' => 'links.edit',
                'mySelfSee' => 'links.see',
                'name' => 'flat',
                'label' => 'flat',
                'is_active' => 'boolean',
                'defaultStatus' => 'relations.belongsTo',
                'allows_overlaps' => 'boolean',
                'requires_operator' => 'boolean',
                'requires_tool' => 'boolean',
                'requires_processable' => 'boolean',
                'sort_index' => 'flat',
                'color' => 'flat',
                'icon' => 'flat',
                'mySelfDelete' => 'links.delete',
            ],
        ];
    }
}

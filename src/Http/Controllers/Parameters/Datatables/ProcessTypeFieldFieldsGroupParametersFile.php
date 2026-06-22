<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Datatables;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ProcessTypeFieldFieldsGroupParametersFile extends FieldsGroupParametersFile
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
                'name' => 'flat',
                'label' => 'flat',
                'type' => 'flat',
                'is_required' => 'boolean',
                'sort_index' => 'flat',
                'mySelfDelete' => 'links.delete',
            ],
        ];
    }
}

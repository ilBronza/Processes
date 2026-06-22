<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Datatables;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ProcessStatusFieldsGroupParametersFile extends FieldsGroupParametersFile
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
                'is_initial' => 'boolean',
                'is_final' => 'boolean',
                'is_positive' => 'boolean',
                'is_negative' => 'boolean',
                'is_blocking' => 'boolean',
                'sort_index' => 'flat',
                'color' => 'flat',
                'icon' => 'flat',
                'mySelfDelete' => 'links.delete',
            ],
        ];
    }
}

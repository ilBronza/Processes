<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Datatables;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ProcessStatusLogFieldsGroupParametersFile extends FieldsGroupParametersFile
{
    public static function getFieldsGroup(): array
    {
        return [
            'translationPrefix' => 'processes::fields',
            'fields' => [
                'mySelfPrimary' => 'primary',
                'mySelfSee' => 'links.see',
                'process' => 'relations.belongsTo',
                'previousStatus' => 'relations.belongsTo',
                'status' => 'relations.belongsTo',
                'user' => 'relations.belongsTo',
                'set_at' => 'dates.datetime',
                'reason' => 'flat',
                'notes' => 'flat',
            ],
        ];
    }
}

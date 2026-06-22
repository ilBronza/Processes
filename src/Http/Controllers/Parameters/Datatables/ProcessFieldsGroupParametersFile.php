<?php

namespace IlBronza\Processes\Http\Controllers\Parameters\Datatables;

use IlBronza\Datatables\Providers\FieldsGroupParametersFile;

class ProcessFieldsGroupParametersFile extends FieldsGroupParametersFile
{
    public static function getFieldsGroup(): array
    {
        return [
            'translationPrefix' => 'processes::fields',
            'fields' => [
                'mySelfPrimary' => 'primary',
                'mySelfEdit' => 'links.edit',
                'mySelfSee' => 'links.see',
                'type' => 'relations.belongsTo',
                'status' => 'relations.belongsTo',
                'operator' => 'relations.belongsTo',
                'user' => 'relations.belongsTo',
                'processable_type' => 'flat',
                'processable_id' => 'flat',
                'toolable_type' => 'flat',
                'toolable_id' => 'flat',
                'planned_starts_at' => 'dates.datetime',
                'planned_ends_at' => 'dates.datetime',
                'starts_at' => 'dates.datetime',
                'ends_at' => 'dates.datetime',
                'duration_seconds' => 'flat',
                'is_locked' => 'boolean',
                'parent' => 'relations.belongsTo',
                'external_source' => 'flat',
                'external_id' => 'flat',
                'mySelfDelete' => 'links.delete',
            ],
        ];
    }
}

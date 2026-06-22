<?php

namespace IlBronza\Processes\Database\Seeders;

use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessStatusTransition;
use IlBronza\Processes\Models\ProcessType;
use Illuminate\Database\Seeder;

class ProcessesDefaultSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = collect(config('processes.defaults.statuses', []))
            ->mapWithKeys(function (array $status, int $index) {
                $model = ProcessStatus::getProjectClassName()::updateOrCreate(
                    ['name' => $status['name']],
                    array_merge([
                        'label' => ucfirst(str_replace('_', ' ', $status['name'])),
                        'is_active' => true,
                        'sort_index' => $index,
                    ], $status)
                );

                return [$model->name => $model];
            });

        $initialStatus = $statuses->firstWhere('is_initial', true) ?? $statuses->first();

        collect(config('processes.defaults.types', []))->each(function (array $type, int $index) use ($initialStatus) {
            ProcessType::getProjectClassName()::updateOrCreate(
                ['name' => $type['name']],
                array_merge([
                    'label' => ucfirst(str_replace('_', ' ', $type['name'])),
                    'is_active' => true,
                    'sort_index' => $index,
                    'default_status_id' => $initialStatus?->getKey(),
                ], $type)
            );
        });

        $this->seedTransitions($statuses);
    }

    protected function seedTransitions($statuses): void
    {
        $transitions = [
            ['from' => 'draft', 'to' => 'planned'],
            ['from' => 'planned', 'to' => 'started'],
            ['from' => 'started', 'to' => 'paused'],
            ['from' => 'paused', 'to' => 'started'],
            ['from' => 'started', 'to' => 'completed'],
            ['from' => 'planned', 'to' => 'cancelled', 'requires_reason' => true],
            ['from' => 'started', 'to' => 'failed', 'requires_reason' => true],
        ];

        foreach($transitions as $transition)
        {
            if(! ($from = $statuses->get($transition['from'])))
                continue;

            if(! ($to = $statuses->get($transition['to'])))
                continue;

            ProcessStatusTransition::getProjectClassName()::updateOrCreate(
                [
                    'process_type_id' => null,
                    'from_status_id' => $from->getKey(),
                    'to_status_id' => $to->getKey(),
                ],
                [
                    'is_allowed' => true,
                    'requires_reason' => $transition['requires_reason'] ?? false,
                ]
            );
        }
    }
}

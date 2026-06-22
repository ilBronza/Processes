<?php

namespace IlBronza\Processes;

use IlBronza\CRUD\Providers\RouterProvider\IbRouter;
use IlBronza\CRUD\Providers\RouterProvider\RoutedObjectInterface;

class Processes implements RoutedObjectInterface
{
    public function manageMenuButtons()
    {
        if(! $menu = app('menu'))
            return;

        $settingsButton = $menu->provideButton([
            'text' => 'menu::menu.settings',
            'name' => 'settings',
            'icon' => 'gear',
            'roles' => ['administrator'],
        ]);

        $processesManagerButton = $menu->createButton([
            'name' => 'processesManager',
            'icon' => 'diagram-project',
            'text' => 'processes::processes.list',
        ]);

        $settingsButton->addChild($processesManagerButton);

        foreach([
            'processes.index' => 'processes::processes.list',
            'processTypes.index' => 'processes::processTypes.list',
            'processStatuses.index' => 'processes::processStatuses.list',
            'processStatusTransitions.index' => 'processes::processStatusTransitions.list',
            'processTypeFields.index' => 'processes::processTypeFields.list',
            'processStatusLogs.index' => 'processes::processStatusLogs.list',
        ] as $route => $text)
            $processesManagerButton->addChild($menu->createButton([
                'name' => $route,
                'icon' => 'gear',
                'text' => $text,
                'href' => IbRouter::route($this, $route),
            ]));
    }

    public function getRoutePrefix(): ?string
    {
        return config('processes.routePrefix');
    }

    public static function getController(string $target, string $controllerPrefix): string
    {
        return config("processes.models.{$target}.controllers.{$controllerPrefix}");
    }
}

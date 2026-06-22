<?php

namespace IlBronza\Processes;

use IlBronza\Processes\Http\Middleware\ProcessesMiddlewareRolesPermissions;
use Illuminate\Support\ServiceProvider;

class ProcessesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'processes');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if(config('processes.crud.enabled', true))
            $this->loadRoutesFrom(__DIR__ . '/../routes/processes.php');

        $this->app['router']->aliasMiddleware('processes.roles', ProcessesMiddlewareRolesPermissions::class);

        if($this->app->runningInConsole())
            $this->bootForConsole();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/processes.php', 'processes');

        $this->app->singleton('processes', function () {
            return new Processes;
        });
    }

    public function provides()
    {
        return ['processes'];
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__ . '/../config/processes.php' => config_path('processes.php'),
        ], 'processes.config');
    }
}

<?php

namespace IlBronza\Processes\Tests;

use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessType;
use IlBronza\Processes\ProcessesServiceProvider;
use IlBronza\Processes\Helpers\ProcessCreatorHelper;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Validation\ValidationException;

abstract class TestCase extends OrchestraTestCase
{
    protected ProcessStatus $draft;
    protected ProcessType $type;

    protected function getPackageProviders($app)
    {
        return [
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
            \Spatie\Permission\PermissionServiceProvider::class,
            \IlBronza\AccountManager\AccountManagerServiceProvider::class,
            \IlBronza\Menu\MenuServiceProvider::class,
            \IlBronza\FormField\FormFieldServiceProvider::class,
            \IlBronza\Form\FormServiceProvider::class,
            ProcessesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('app.locale', 'it');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]);
        $app['config']->set('activitylog.enabled', false);
        $app['config']->set('crud.broadcast.enabled', false);
        $app['config']->set('processes.crud.enabled', false);
        $app['config']->set('menu.serviceProviders', []);
        $app['config']->set('auth.providers.users.model', \App\Models\User::class);
    }

    protected function defineDatabaseMigrations()
    {
        $permissionProvider = new \ReflectionClass(\Spatie\Permission\PermissionServiceProvider::class);
        $migration = require dirname($permissionProvider->getFileName()) . '/../database/migrations/create_permission_tables.php.stub';
        $migration->up();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->draft = ProcessStatus::create(['name' => 'draft', 'label' => 'Bozza', 'is_initial' => true, 'is_active' => true]);
        $this->type = ProcessType::create(['name' => 'test', 'label' => 'Test', 'default_status_id' => $this->draft->id, 'is_active' => true]);
    }

    protected function process(array $attributes = [])
    {
        return ProcessCreatorHelper::create(array_merge(['process_type_id' => $this->type->id], $attributes));
    }

    protected function assertInvalid(string $field, callable $operation): void
    {
        try {
            $operation();
            $this->fail('Expected a validation error for ' . $field);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}

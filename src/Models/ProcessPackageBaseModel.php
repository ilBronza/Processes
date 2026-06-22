<?php

namespace IlBronza\Processes\Models;

use IlBronza\CRUD\Models\PackagedBaseModel;
use IlBronza\CRUD\Traits\Model\CRUDUseUuidTrait;
use Illuminate\Support\Str;

class ProcessPackageBaseModel extends PackagedBaseModel
{
    use CRUDUseUuidTrait;

    protected $keyType = 'string';

    public static $packageConfigPrefix = 'processes';

    public function getRouteBaseNamePrefix(): string
    {
        return config('processes.routePrefix');
    }

    public static function getModelConfigPrefix(): string
    {
        return static::$modelConfigPrefix ?? Str::camel(class_basename(static::class));
    }

    public static function getProjectClassName(): string
    {
        return config('processes.models.' . static::getModelConfigPrefix() . '.class');
    }

    public function getTable(): string
    {
        return config('processes.models.' . static::getModelConfigPrefix() . '.table');
    }

    public static function getByKey(string $id): static
    {
        return static::getProjectClassName()::findOrFail($id);
    }
}

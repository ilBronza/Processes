<?php

namespace IlBronza\Processes\Http\Controllers;

use IlBronza\CRUD\CRUD;
use IlBronza\CRUD\Traits\CRUDShowTrait;

class CrudProcessesCrudController extends CRUD
{
    use CRUDShowTrait;

    public function getBaseConfigName()
    {
        return 'processes';
    }

    public function getRouteBaseNamePrefix(): ?string
    {
        return config($this->getBaseConfigName() . '.routePrefix');
    }

    public function setModelClass()
    {
        $this->modelClass = config($this->getBaseConfigName() . ".models.{$this->configModelClassName}.class");
    }

    public function getModelClass(): string
    {
        return config($this->getBaseConfigName() . ".models.{$this->configModelClassName}.class");
    }

    public function getGenericParametersFile(): ?string
    {
        return config($this->getBaseConfigName() . ".models.{$this->configModelClassName}.parametersFiles.create");
    }

    public function getIndexFieldsArray()
    {
        return config($this->getBaseConfigName() . ".models.{$this->configModelClassName}.fieldsGroupsFiles.index")::getTracedFieldsGroup();
    }

    public function getRelatedFieldsArray()
    {
        return config($this->getBaseConfigName() . ".models.{$this->configModelClassName}.fieldsGroupsFiles.related")::getTracedFieldsGroup();
    }

    public function getIndexElements()
    {
        return $this->getModelClass()::query()->get();
    }
}

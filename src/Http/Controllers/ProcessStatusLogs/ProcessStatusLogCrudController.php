<?php

namespace IlBronza\Processes\Http\Controllers\ProcessStatusLogs;

use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\Processes\Http\Controllers\CrudProcessesCrudController;

class ProcessStatusLogCrudController extends CrudProcessesCrudController
{
    use CRUDIndexTrait;
    use CRUDPlainIndexTrait;
    use CRUDShowTrait;

    public $configModelClassName = 'processStatusLog';
    public $avoidCreateButton = true;
    public $allowedMethods = ['index', 'show'];

    public function getIndexElements()
    {
        return $this->getModelClass()::with(['process', 'status', 'previousStatus', 'user'])->latest('set_at')->get();
    }

    public function show(string $processStatusLog)
    {
        return $this->_show($this->findModel($processStatusLog));
    }
}

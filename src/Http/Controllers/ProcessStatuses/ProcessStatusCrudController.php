<?php

namespace IlBronza\Processes\Http\Controllers\ProcessStatuses;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use IlBronza\CRUD\Traits\CRUDDeleteTrait;
use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\Processes\Http\Controllers\CrudProcessesCrudController;
use Illuminate\Http\Request;

class ProcessStatusCrudController extends CrudProcessesCrudController
{
    use CRUDIndexTrait;
    use CRUDPlainIndexTrait;
    use CRUDCreateStoreTrait;
    use CRUDEditUpdateTrait;
    use CRUDShowTrait;
    use CRUDDeleteTrait;

    public $configModelClassName = 'processStatus';
    public $saveAndNew = true;
    public $allowedMethods = ['index', 'create', 'store', 'edit', 'update', 'show', 'destroy'];

    public function getIndexElements()
    {
        return $this->getModelClass()::ordered()->get();
    }

    public function show(string $processStatus) { return $this->_show($this->findModel($processStatus)); }
    public function edit(string $processStatus) { return $this->_edit($this->findModel($processStatus)); }
    public function update(Request $request, string $processStatus) { return $this->_update($request, $this->findModel($processStatus)); }
    public function destroy(string $processStatus) { return $this->_destroy($this->findModel($processStatus)); }
}

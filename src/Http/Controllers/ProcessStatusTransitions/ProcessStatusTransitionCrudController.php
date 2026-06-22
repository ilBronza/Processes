<?php

namespace IlBronza\Processes\Http\Controllers\ProcessStatusTransitions;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use IlBronza\CRUD\Traits\CRUDDeleteTrait;
use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\Processes\Http\Controllers\CrudProcessesCrudController;
use Illuminate\Http\Request;

class ProcessStatusTransitionCrudController extends CrudProcessesCrudController
{
    use CRUDIndexTrait;
    use CRUDPlainIndexTrait;
    use CRUDCreateStoreTrait;
    use CRUDEditUpdateTrait;
    use CRUDShowTrait;
    use CRUDDeleteTrait;

    public $configModelClassName = 'processStatusTransition';
    public $saveAndNew = true;
    public $allowedMethods = ['index', 'create', 'store', 'edit', 'update', 'show', 'destroy'];

    public function getIndexElements()
    {
        return $this->getModelClass()::with(['processType', 'fromStatus', 'toStatus'])->get();
    }

    public function show(string $processStatusTransition) { return $this->_show($this->findModel($processStatusTransition)); }
    public function edit(string $processStatusTransition) { return $this->_edit($this->findModel($processStatusTransition)); }
    public function update(Request $request, string $processStatusTransition) { return $this->_update($request, $this->findModel($processStatusTransition)); }
    public function destroy(string $processStatusTransition) { return $this->_destroy($this->findModel($processStatusTransition)); }
}

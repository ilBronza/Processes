<?php

namespace IlBronza\Processes\Http\Controllers\ProcessTypes;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use IlBronza\CRUD\Traits\CRUDDeleteTrait;
use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\Processes\Http\Controllers\CrudProcessesCrudController;
use Illuminate\Http\Request;

class ProcessTypeCrudController extends CrudProcessesCrudController
{
    use CRUDIndexTrait;
    use CRUDPlainIndexTrait;
    use CRUDCreateStoreTrait;
    use CRUDEditUpdateTrait;
    use CRUDShowTrait;
    use CRUDDeleteTrait;

    public $configModelClassName = 'processType';
    public $saveAndNew = true;
    public $allowedMethods = ['index', 'create', 'store', 'edit', 'update', 'show', 'destroy'];

    public function getIndexElements()
    {
        return $this->getModelClass()::with(['defaultStatus'])->ordered()->get();
    }

    public function show(string $processType) { return $this->_show($this->findModel($processType)); }
    public function edit(string $processType) { return $this->_edit($this->findModel($processType)); }
    public function update(Request $request, string $processType) { return $this->_update($request, $this->findModel($processType)); }
    public function destroy(string $processType) { return $this->_destroy($this->findModel($processType)); }
}

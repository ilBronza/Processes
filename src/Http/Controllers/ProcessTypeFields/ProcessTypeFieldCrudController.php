<?php

namespace IlBronza\Processes\Http\Controllers\ProcessTypeFields;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use IlBronza\CRUD\Traits\CRUDDeleteTrait;
use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\Processes\Http\Controllers\CrudProcessesCrudController;
use Illuminate\Http\Request;

class ProcessTypeFieldCrudController extends CrudProcessesCrudController
{
    use CRUDIndexTrait;
    use CRUDPlainIndexTrait;
    use CRUDCreateStoreTrait;
    use CRUDEditUpdateTrait;
    use CRUDShowTrait;
    use CRUDDeleteTrait;

    public $configModelClassName = 'processTypeField';
    public $saveAndNew = true;
    public $allowedMethods = ['index', 'create', 'store', 'edit', 'update', 'show', 'destroy'];

    public function getIndexElements()
    {
        return $this->getModelClass()::with(['processType'])->get();
    }

    public function show(string $processTypeField) { return $this->_show($this->findModel($processTypeField)); }
    public function edit(string $processTypeField) { return $this->_edit($this->findModel($processTypeField)); }
    public function update(Request $request, string $processTypeField) { return $this->_update($request, $this->findModel($processTypeField)); }
    public function destroy(string $processTypeField) { return $this->_destroy($this->findModel($processTypeField)); }
}

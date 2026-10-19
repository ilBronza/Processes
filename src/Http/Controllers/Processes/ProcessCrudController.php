<?php

namespace IlBronza\Processes\Http\Controllers\Processes;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use IlBronza\CRUD\Traits\CRUDDeleteTrait;
use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDRelationshipTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\CRUD\Helpers\CrudRequestHelper;
use IlBronza\Processes\Helpers\Crud\ProcessCrudStorer;
use IlBronza\Processes\Helpers\Crud\ProcessCrudUpdater;
use IlBronza\Processes\Helpers\Crud\ProcessCrudEditor;
use IlBronza\Processes\Http\Controllers\CrudProcessesCrudController;
use Illuminate\Http\Request;

class ProcessCrudController extends CrudProcessesCrudController
{
    use CRUDIndexTrait;
    use CRUDPlainIndexTrait;
    use CRUDCreateStoreTrait;
    use CRUDEditUpdateTrait;
    use CRUDShowTrait;
    use CRUDDeleteTrait;
    use CRUDRelationshipTrait;

    public $configModelClassName = 'process';
    public $saveAndNew = true;

    public $allowedMethods = ['index', 'create', 'store', 'edit', 'update', 'show', 'destroy'];

    public function getIndexElements()
    {
        return $this->getModelClass()::with(['type', 'status', 'operator', 'user', 'parent'])->get();
    }

    public function show(string $process)
    {
        return $this->_show($this->findModel($process));
    }

    public function edit(string $process)
    {
        return $this->_edit($this->findModel($process));
    }

    public function update(Request $request, string $process)
    {
        return $this->_update($request, $this->findModel($process));
    }

    public function getUpdaterHelperClassName(): string
    {
        return ProcessCrudUpdater::class;
    }

    public function _store(Request $request)
    {
        $this->checkUserStoringRights();
        $this->modelInstance = ProcessCrudStorer::saveByRequest(
            $this->makeModel(), $this->getStoreParametersClass(), $request
        );
        $this->sendStoreSuccessMessage();
        $this->performAdditionalOperations();

        if($this->shouldCloseIframeAfterStore($request))
            return $this->closeIframeAfterStore($request);
        if(CrudRequestHelper::isSaveAndCopy($request))
            return redirect()->to($this->modelInstance->getEditUrl());

        return redirect()->to($this->getAfterStoredRedirectUrl());
    }

    public function updateEditorBatch(Request $request)
    {
        return ProcessCrudEditor::saveBatchByRequest(
            $this->getModel(), $this->getUpdateParametersClass(), $request,
            $this->getUpdateEvents(), $this->getUpdateCallback()
        );
    }

    private function manageUpdateGeneric(Request $request)
    {
        $this->modelInstance = ProcessCrudEditor::saveByRequest(
            $this->getModel(), $this->getUpdateParametersClass(), $request
        );
        $this->broadcastCrudModel($this->modelInstance, 'updated');
        $field = $request->input('field');
        $result = [
            'success' => true, 'update-editor' => true, 'model-id' => $this->getModel()->getKey(),
            'field' => $field, 'value' => $this->getModel()->{$field}, $field => $this->getModel()->{$field},
        ];
        if($action = $this->getUpdatingFormFieldInstance($request)->getEditorAction())
        {
            $result['ibaction'] = true;
            $result['action'] = $action;
        }

        return $this->returnUpdateParameters($request, $result);
    }

    public function destroy(string $process)
    {
        return $this->_destroy($this->findModel($process));
    }
}

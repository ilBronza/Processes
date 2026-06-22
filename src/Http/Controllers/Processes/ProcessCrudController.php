<?php

namespace IlBronza\Processes\Http\Controllers\Processes;

use IlBronza\CRUD\Traits\CRUDCreateStoreTrait;
use IlBronza\CRUD\Traits\CRUDDeleteTrait;
use IlBronza\CRUD\Traits\CRUDEditUpdateTrait;
use IlBronza\CRUD\Traits\CRUDIndexTrait;
use IlBronza\CRUD\Traits\CRUDPlainIndexTrait;
use IlBronza\CRUD\Traits\CRUDRelationshipTrait;
use IlBronza\CRUD\Traits\CRUDShowTrait;
use IlBronza\Processes\Helpers\ProcessStatusSetterHelper;
use IlBronza\Processes\Http\Controllers\CrudProcessesCrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $process = $this->findModel($process);
        $targetStatusId = $request->input('process_status_id');
        $shouldChangeStatus = $targetStatusId && ($targetStatusId !== $process->process_status_id);

        if(! $shouldChangeStatus)
            return $this->_update($request, $process);

        return DB::transaction(function () use ($request, $process, $targetStatusId) {
            $request->merge(['process_status_id' => $process->process_status_id]);

            $response = $this->_update($request, $process);

            ProcessStatusSetterHelper::setStatus($process->refresh(), $targetStatusId, [
                'reason' => $request->input('status_reason'),
                'user' => auth()->user(),
            ]);

            return $response;
        });
    }

    public function destroy(string $process)
    {
        return $this->_destroy($this->findModel($process));
    }
}

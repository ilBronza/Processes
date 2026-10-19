<?php

namespace IlBronza\Processes\Tests\Fixtures;

use IlBronza\Processes\Http\Controllers\Processes\ProcessCrudController;
use IlBronza\Processes\Http\Controllers\Parameters\Fieldsets\ProcessFieldsetsParameters;
use IlBronza\Form\Helpers\FieldsetsProvider\FieldsetParametersFile;
use Illuminate\Support\Facades\Gate;

// Keep production persistence, replacing host-app authorization and presentation only.
class ProcessController extends ProcessCrudController
{
    public function __construct() { $this->setModelClass(); }
    public function checkUserStoringRights() { Gate::authorize('write-process'); }
    public function checkIfUserCanUpdate() { Gate::authorize('write-process'); }
    public function getAfterStoredRedirectUrl() { return '/test/processes'; }
    public function getAfterUpdatedRedirectUrl() { return '/test/processes'; }
    public function sendUpdateSuccessMessage() {}
    public function getUpdateParametersClass(): ?FieldsetParametersFile { return new ProcessFieldsetsParameters(); }
}

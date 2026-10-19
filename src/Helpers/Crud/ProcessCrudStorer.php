<?php

namespace IlBronza\Processes\Helpers\Crud;

use IlBronza\CRUD\Helpers\ModelManagers\CrudModelStorer;

class ProcessCrudStorer extends CrudModelStorer
{
    use PersistsProcesses;
}

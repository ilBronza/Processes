<?php

namespace IlBronza\Processes\Contracts;

use IlBronza\Processes\Models\ProcessStatus;

interface ProcessStatusResolverContract
{
    public function resolveProcessStatus(mixed $value): ProcessStatus;
}

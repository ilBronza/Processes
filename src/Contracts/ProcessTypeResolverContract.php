<?php

namespace IlBronza\Processes\Contracts;

use IlBronza\Processes\Models\ProcessType;

interface ProcessTypeResolverContract
{
    public function resolveProcessType(mixed $value): ProcessType;
}

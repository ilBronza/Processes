<?php

namespace IlBronza\Processes\Facades;

use Illuminate\Support\Facades\Facade;

class Processes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'processes';
    }
}

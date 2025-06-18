<?php

namespace MediaWebId\MakeModule\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MediaWebId\MakeModule\MakeModule
 */
class MakeModule extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MediaWebId\MakeModule\MakeModule::class;
    }
}

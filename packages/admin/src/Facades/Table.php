<?php

namespace GetCandy\Hub\Facades;

use GetCandy\Hub\Tables\TableRegistry;
use Illuminate\Support\Facades\Facade;

class Table extends Facade
{
    /**
     * Return the facade class reference.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TableRegistry::class;
    }
}

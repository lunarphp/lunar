<?php

namespace Lunar\Search\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Search\Contracts\SearchManagerContract;

class Search extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SearchManagerContract::class;
    }
}

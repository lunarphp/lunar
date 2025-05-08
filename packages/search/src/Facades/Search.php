<?php

namespace Lunar\Search\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Search\Contracts\SearchManagerContract;
use Lunar\Search\Engines\AbstractEngine;
use Lunar\Search\Engines\DatabaseEngine;
use Lunar\Search\Engines\MeilisearchEngine;
use Lunar\Search\Engines\TypesenseEngine;
use Lunar\Search\SearchManager;

/**
 * @method static DatabaseEngine createDatabaseDriver()
 * @method static MeilisearchEngine createMeilisearchDriver()
 * @method static TypesenseEngine createTypesenseDriver()
 * @method static mixed buildProvider()
 * @method static SearchManager model()
 * @method static AbstractEngine driver()
 * @method static string getDefaultDriver()
 */
class Search extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SearchManagerContract::class;
    }
}

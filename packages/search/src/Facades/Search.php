<?php

namespace Lunar\Search\Facades;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;
use Lunar\Search\Contracts\SearchManagerContract;
use Lunar\Search\Engines\AbstractEngine;
use Lunar\Search\SearchManager;

/**
 * @method static void createDatabaseDriver()
 * @method static void createMeilisearchDriver()
 * @method static void createTypesenseDriver()
 * @method static void buildProvider(void $provider)
 * @method static SearchManager model(string $model)
 * @method static AbstractEngine driver(string|null $driver = null)
 * @method static void getDefaultDriver()
 * @method static SearchManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static Container getContainer()
 * @method static SearchManager setContainer(Container $container)
 * @method static SearchManager forgetDrivers()
 *
 * @see SearchManager
 */
class Search extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SearchManagerContract::class;
    }
}

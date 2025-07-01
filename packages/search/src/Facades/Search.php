<?php

namespace Lunar\Search\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Search\Contracts\SearchManagerContract;

/**
 * @method static void createDatabaseDriver()
 * @method static void createMeilisearchDriver()
 * @method static void createTypesenseDriver()
 * @method static void buildProvider(void $provider)
 * @method static \Lunar\Search\SearchManager model(string $model)
 * @method static \Lunar\Search\Engines\AbstractEngine driver(string|null $driver = null)
 * @method static void getDefaultDriver()
 * @method static \Lunar\Search\SearchManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Lunar\Search\SearchManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \Lunar\Search\SearchManager forgetDrivers()
 *
 * @see \Lunar\Search\SearchManager
 */
class Search extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SearchManagerContract::class;
    }
}

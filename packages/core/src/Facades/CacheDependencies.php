<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void define(string $name, array|\Closure $definition)
 * @method static array|\Closure|null get(string $name)
 * @method static bool has(string $name)
 * @method static void flush()
 *
 * @see \Lunar\Core\Contracts\CacheDependencies
 */
class CacheDependencies extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lunar\Core\Contracts\CacheDependencies::class;
    }
}

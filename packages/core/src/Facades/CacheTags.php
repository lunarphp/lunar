<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\DependencyResolver;

/**
 * @method static array for(\Illuminate\Database\Eloquent\Model $root, ?string $graph = null)
 *
 * @see DependencyResolver
 */
class CacheTags extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DependencyResolver::class;
    }
}

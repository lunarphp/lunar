<?php

namespace Lunar\Api\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Api\Contracts\ApiManager;

/**
 * @method static \Lunar\Api\Registry\SurfaceRegistry storefront(string $version = 'v1')
 * @method static \Lunar\Api\Registry\SurfaceRegistry admin(string $version = 'v1')
 * @method static \Lunar\Api\Registry\SurfaceRegistry surface(string $surface, string $version)
 * @method static array<string, \Lunar\Api\Registry\SurfaceRegistry> surfaces()
 *
 * @see \Lunar\Api\ApiManager
 */
class Api extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApiManager::class;
    }
}

<?php

namespace Lunar\Filament\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Filament\Support\ComponentExtensions\Registry;

/**
 * @method static Registry register(array $extensions)
 * @method static array all()
 * @method static array for(string $target)
 * @method static mixed callHook(string $target, ?object $caller, string $hookName, mixed ...$args)
 *
 * @see Registry
 */
class LunarFilament extends Facade
{
    public static function extensions(array $extensions): Registry
    {
        return static::getFacadeRoot()->register($extensions);
    }

    protected static function getFacadeAccessor(): string
    {
        return Registry::class;
    }
}

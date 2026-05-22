<?php

namespace Lunar\Filament\Support\Concerns;

use Lunar\Filament\Support\Facades\LunarFilament;

trait CallsHooks
{
    protected function callLunarHook(string $hookName, mixed ...$args): mixed
    {
        return LunarFilament::callHook(static::class, $this, $hookName, ...$args);
    }

    protected static function callStaticLunarHook(string $hookName, mixed ...$args): mixed
    {
        return LunarFilament::callHook(static::class, null, $hookName, ...$args);
    }
}

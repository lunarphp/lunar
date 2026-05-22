<?php

namespace Lunar\Filament\Support\Concerns;

use Lunar\Filament\Support\Facades\LunarFilament;

trait CallsHooks
{
    protected function callLunarHook(...$args)
    {
        return LunarFilament::callHook(static::class, $this, ...$args);
    }

    protected static function callStaticLunarHook(...$args)
    {
        return LunarFilament::callHook(static::class, null, ...$args);
    }
}

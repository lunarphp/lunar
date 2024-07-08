<?php

namespace Lunar\Admin\Support\Concerns;

use Lunar\Admin\Support\Facades\LunarPanel;

trait CallsStaticHooks
{
    protected static function callLunarHook(...$args)
    {
        return LunarPanel::callHook(static::class, null, ...$args);
    }
}

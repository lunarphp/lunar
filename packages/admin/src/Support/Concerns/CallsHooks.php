<?php

namespace Lunar\Admin\Support\Concerns;

use Lunar\Admin\Support\Facades\LunarPanel;

trait CallsHooks
{
    protected function callLunarHook(...$args)
    {
        return LunarPanel::callHook(static::class, $this, ...$args);
    }

    protected static function callStaticLunarHook(...$args)
    {
        return LunarPanel::callHook(static::class, null, ...$args);
    }
}

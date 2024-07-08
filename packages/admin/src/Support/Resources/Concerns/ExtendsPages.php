<?php

namespace Lunar\Admin\Support\Resources\Concerns;

trait ExtendsPages
{
    public static function getPages(): array
    {
        return self::callStaticLunarHook('extendPages', static::getDefaultPages());
    }

    protected static function getDefaultPages(): array
    {
        return [];
    }
}

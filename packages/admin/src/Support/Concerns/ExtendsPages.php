<?php

namespace Lunar\Admin\Support\Concerns;

trait ExtendsPages
{
    public static function getPages(): array
    {
        return self::callLunarHook('getPages', static::getDefaultPages());
    }

    protected static function getDefaultPages(): array
    {
        return [];
    }
}

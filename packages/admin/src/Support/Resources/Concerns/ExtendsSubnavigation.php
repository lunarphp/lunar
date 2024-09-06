<?php

namespace Lunar\Admin\Support\Resources\Concerns;

use Filament\Pages\Page;

trait ExtendsSubnavigation
{
    public static function getRecordSubNavigation(Page $page): array
    {
        $pages = self::callStaticLunarHook('extendSubNavigation', static::getDefaultSubnavigation());

        return $page->generateNavigationItems($pages);
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [];
    }
}

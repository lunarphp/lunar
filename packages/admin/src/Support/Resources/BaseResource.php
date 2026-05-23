<?php

namespace Lunar\Admin\Support\Resources;

use Filament\Pages\Page;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Resources\Resource;
use Lunar\Admin\Support\Resources\Concerns\HasLunarPermissions;
use Lunar\Admin\Support\Resources\Concerns\HasScoutGlobalSearch;
use Lunar\Admin\Support\Resources\Concerns\ResolvesModelContract;
use Lunar\Filament\Support\Concerns\CallsHooks;

class BaseResource extends Resource
{
    use CallsHooks;
    use HasLunarPermissions;
    use HasScoutGlobalSearch;
    use ResolvesModelContract;

    public static function getPages(): array
    {
        return self::callStaticLunarHook('extendPages', static::getDefaultPages());
    }

    protected static function getDefaultPages(): array
    {
        return [];
    }

    /**
     * @return array<class-string<RelationManager> | RelationGroup | RelationManagerConfiguration>
     */
    public static function getRelations(): array
    {
        return self::callStaticLunarHook('getRelations', static::getDefaultRelations());
    }

    protected static function getDefaultRelations(): array
    {
        return [];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $pages = self::callStaticLunarHook('extendSubNavigation', static::getDefaultSubNavigation());

        return $page->generateNavigationItems($pages);
    }

    protected static function getDefaultSubNavigation(): array
    {
        return [];
    }
}

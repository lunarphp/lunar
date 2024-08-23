<?php

namespace Lunar\Admin\Support\Resources\Concerns;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;

trait ExtendsRelationManagers
{
    /**
     * @return array<class-string<RelationManager> | RelationGroup | RelationManagerConfiguration>
     */
    public static function getRelations(): array
    {
        return static::callStaticLunarHook('getRelations', static::getDefaultRelations());
    }

    protected static function getDefaultRelations(): array
    {
        return [
            //
        ];
    }
}

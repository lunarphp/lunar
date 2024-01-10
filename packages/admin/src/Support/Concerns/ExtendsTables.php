<?php

namespace Lunar\Admin\Support\Concerns;

use Filament\Tables\Table;

trait ExtendsTables
{
    public static function getTableFilters(): array
    {
        return self::callLunarHook('extendTableFilters', static::getDefaultFilters());
    }

    public static function getDefaultFilters(): array
    {
        return [];
    }

    public static function table(Table $table): Table
    {
        return self::callLunarHook('extendTable', static::getDefaultTable($table));
    }

    protected static function getDefaultTable(Table $table): Table
    {
        return $table;
    }
}

<?php

namespace Lunar\Admin\Support\Concerns;

use Filament\Tables\Table;

trait ExtendsTables
{
    public static function table(Table $table): Table
    {
        return self::callLunarHook('extendTable', static::getDefaultTable($table));
    }

    protected static function getDefaultTable(Table $table): Table
    {
        return $table;
    }
}

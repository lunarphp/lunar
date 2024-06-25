<?php

namespace Lunar\Admin\Support\Concerns\RelationManagers;

use Filament\Tables\Table;

trait ExtendsTables
{
    public function table(Table $table): Table
    {
        return self::callLunarHook('extendTable', $this->getDefaultTable($table));
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table;
    }
}

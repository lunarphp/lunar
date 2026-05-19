<?php

namespace Lunar\Admin\Support\Pages\Concerns;

use Filament\Tables\Table;

trait ExtendsTables
{
    public function table(Table $table): Table
    {
        return $this->callLunarHook('extendTable', $this->getDefaultTable($table));
    }

    protected function getDefaultTable(Table $table): Table
    {
        return $table;
    }
}

<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Tables\Table;

abstract class ListPageExtension extends BaseExtension
{
    public function extendTable(Table $table): Table
    {
        return $table;
    }

    public function relationManagers(array $managers): array
    {
        return $managers;
    }
}

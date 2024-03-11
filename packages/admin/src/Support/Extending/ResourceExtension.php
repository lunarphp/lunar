<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Tables\Table;

abstract class ResourceExtension extends BaseExtension
{
    public function getRelations(array $managers): array
    {
        return $managers;
    }

    public function extendTable(Table $table): Table
    {
        return $table;
    }
}

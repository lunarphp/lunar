<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Schemas\Schema;
use Filament\Tables\Table;

abstract class RelationManagerExtension extends BaseExtension
{
    public function extendForm(Schema $schema): Schema
    {
        return $schema;
    }

    public function extendTable(Table $table): Table
    {
        return $table;
    }
}

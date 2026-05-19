<?php

namespace Lunar\Admin\Support\Extending;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

abstract class RelationPageExtension extends BaseExtension
{
    public function extendTable(Table $table): Table
    {
        return $table;
    }

    public function heading($title, Model $record): string
    {
        return $title;
    }

    public function subheading($title, Model $record): ?string
    {
        return $title;
    }
}

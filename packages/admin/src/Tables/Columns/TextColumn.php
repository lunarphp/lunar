<?php

namespace GetCandy\Hub\Tables\Columns;

use Filament\Tables\Columns\TextColumn as FilamentTextColumn;

class TextColumn extends FilamentTextColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.text-column';
}

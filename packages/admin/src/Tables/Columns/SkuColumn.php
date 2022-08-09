<?php

namespace GetCandy\Hub\Tables\Columns;

use Filament\Tables\Columns\TextColumn;

class SkuColumn extends TextColumn
{
    /**
     * {@inheritDoc}
     */
    protected string $view = 'adminhub::tables.columns.sku';

    protected function getStateFromRecord()
    {
        return $this->getRecord()->variants->pluck('sku');
    }
}

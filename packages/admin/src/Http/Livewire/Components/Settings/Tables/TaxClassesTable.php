<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Columns\StatusColumn;
use GetCandy\Models\TaxClass;

class TaxClassesTable extends GetCandyTable
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public bool $filterable = false;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            StatusColumn::make('default'),
            TextColumn::make('name')->url(function ($record) {
                return route('hub.taxe-classes.show', $record->id);
            }),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return TaxClass::paginate($this->perPage);
    }
}

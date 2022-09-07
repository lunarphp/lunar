<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Columns\StatusColumn;
use GetCandy\Models\Currency;

class CurrenciesTable extends GetCandyTable
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
                return route('hub.channels.show', $record->id);
            }),
            TextColumn::make('code'),
            TextColumn::make('exchange_rate'),
            StatusColumn::make('enabled'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Currency::paginate($this->perPage);
    }
}

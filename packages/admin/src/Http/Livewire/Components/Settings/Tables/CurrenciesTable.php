<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\Currency;

class CurrenciesTable extends LunarTable
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
                return route('hub.currencies.show', $record->id);
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

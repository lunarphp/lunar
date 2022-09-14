<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\TaxZone;

class TaxZonesTable extends LunarTable
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
                return route('hub.taxes.show', $record->id);
            }),
            TextColumn::make('zone_type')->heading('type'),
            StatusColumn::make('active'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return TaxZone::paginate($this->perPage);
    }
}

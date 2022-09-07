<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\StatusColumn;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\Models\TaxZone;

class TaxZonesTable extends GetCandyTable
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

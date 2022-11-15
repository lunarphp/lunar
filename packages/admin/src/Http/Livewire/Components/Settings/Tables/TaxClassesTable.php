<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\TaxClass;

class TaxClassesTable extends LunarTable
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

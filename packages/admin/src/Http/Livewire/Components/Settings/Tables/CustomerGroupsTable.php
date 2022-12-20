<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\CustomerGroup;

class CustomerGroupsTable extends LunarTable
{
    use Notifies;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $this->tableBuilder->baseColumns([
            StatusColumn::make('default', function ($record) {
                return $record->default;
            }),
            TextColumn::make('name')->url(function ($record) {
                return route('hub.customer-groups.show', $record->id);
            }),
            TextColumn::make('handle'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return CustomerGroup::paginate($this->perPage);
    }
}

<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\LivewireTables\Components\Table;
use Lunar\Models\Channel;

class ChannelsTable extends Table
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
            TextColumn::make('name')->heading(
                __('adminhub::tables.headings.name')
            )->url(function ($record) {
                return route('hub.channels.show', $record->id);
            }),
            TextColumn::make('handle'),
            TextColumn::make('url'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Channel::paginate($this->perPage);
    }
}

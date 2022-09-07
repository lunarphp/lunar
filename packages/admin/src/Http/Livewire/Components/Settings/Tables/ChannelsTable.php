<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Facades\AttributeManifest;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\LivewireTables\Components\Columns\StatusColumn;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Table;
use GetCandy\Models\Attribute;
use GetCandy\Models\AttributeGroup;
use GetCandy\Models\Channel;
use Illuminate\Support\Str;

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

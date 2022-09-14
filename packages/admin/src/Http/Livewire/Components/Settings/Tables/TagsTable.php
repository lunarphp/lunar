<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\Tag;

class TagsTable extends LunarTable
{
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
            TextColumn::make('value')->url(function ($record) {
                return route('hub.tags.show', $record->id);
            }),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Tag::paginate($this->perPage);
    }
}

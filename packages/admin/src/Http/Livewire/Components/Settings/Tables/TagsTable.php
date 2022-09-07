<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\Models\Tag;

class TagsTable extends GetCandyTable
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

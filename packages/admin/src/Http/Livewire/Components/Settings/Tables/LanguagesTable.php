<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use GetCandy\LivewireTables\Components\Columns\StatusColumn;
use GetCandy\Models\Language;

class LanguagesTable extends GetCandyTable
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
                return route('hub.languages.show', $record->id);
            }),
            TextColumn::make('code'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return Language::paginate($this->perPage);
    }
}

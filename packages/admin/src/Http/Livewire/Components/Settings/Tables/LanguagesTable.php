<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Lunar\Models\Language;

class LanguagesTable extends LunarTable
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

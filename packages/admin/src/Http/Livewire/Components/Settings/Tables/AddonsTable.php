<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Addons\Manifest;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\TextColumn;

class AddonsTable extends LunarTable
{
    use Notifies;

    public $hasPagination = false;

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
            TextColumn::make('name')->url(function ($subject) {
                return route('hub.addons.show', $subject->id);
            }),
            TextColumn::make('version'),
            TextColumn::make('author'),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return app(Manifest::class)->addons()->map(function ($addon) {
            return (object) [
                'id' => $addon['id'],
                'name' => $addon['name'],
                'version' => $addon['version'],
                'author' => $addon['author'],
            ];
        });
    }
}

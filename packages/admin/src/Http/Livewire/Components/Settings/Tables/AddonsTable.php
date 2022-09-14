<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Tables;

use Lunar\Addons\Manifest;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Hub\Tables\LunarTable;
use Lunar\LivewireTables\Components\Columns\StatusColumn;
use Lunar\LivewireTables\Components\Columns\TextColumn;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

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
            StatusColumn::make('verified'),
            StatusColumn::make('licensed'),
            TextColumn::make('version'),
            TextColumn::make('latest_version'),
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
                'id' => $addon['marketplaceId'] ?? Str::random(),
                'licensed' => $addon['licensed'],
                'name' => $addon['name'],
                'verified' => $addon['marketplaceId'] ?? false,
                'version' => $addon['version'],
                'author'    => $addon['author'],
                'latest_version' => $addon['latestVersion'],
            ];
        });
    }
}

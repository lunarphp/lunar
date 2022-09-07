<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Tables;

use GetCandy\Addons\Manifest;
use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use GetCandy\Hub\Tables\GetCandyTable;
use GetCandy\LivewireTables\Components\Columns\StatusColumn;
use GetCandy\LivewireTables\Components\Columns\TextColumn;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class AddonsTable extends GetCandyTable
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

<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Admin\Filament\Resources\CollectionResource;

class ListCollections extends BaseListRecords
{
    protected static string $resource = CollectionResource::class;

    public function mount(): void
    {
        abort(404);
    }

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }
}

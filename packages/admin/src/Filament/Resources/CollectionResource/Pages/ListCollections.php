<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Lunar\Admin\Filament\Resources\CollectionResource;

class ListCollections extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    public function mount(): void
    {
        abort(404);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\CollectionGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Lunar\Admin\Filament\Resources\CollectionGroupResource;

class ListCollectionGroups extends ListRecords
{
    protected static string $resource = CollectionGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

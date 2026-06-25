<?php

namespace Lunar\Admin\Filament\Resources\LocationResource\Pages;

use Filament\Actions\CreateAction;
use Lunar\Admin\Filament\Resources\LocationResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListLocations extends BaseListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\RegionResource\Pages;

use Filament\Actions\CreateAction;
use Lunar\Admin\Filament\Resources\RegionResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListRegions extends BaseListRecords
{
    protected static string $resource = RegionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

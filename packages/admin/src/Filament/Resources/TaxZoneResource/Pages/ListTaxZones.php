<?php

namespace Lunar\Admin\Filament\Resources\TaxZoneResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Lunar\Admin\Filament\Resources\TaxZoneResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListTaxZones extends BaseListRecords
{
    protected static string $resource = TaxZoneResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

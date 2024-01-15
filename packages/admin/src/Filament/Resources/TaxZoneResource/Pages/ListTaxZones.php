<?php

namespace Lunar\Admin\Filament\Resources\TaxZoneResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\TaxZoneResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListTaxZones extends BaseListRecords
{
    protected static string $resource = TaxZoneResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->form([
                TaxZoneResource::getNameFormComponent(),
                TaxZoneResource::getZoneTypeFormComponent(),
                TaxZoneResource::getPriceDisplayFormComponent(),
            ]),
        ];
    }
}

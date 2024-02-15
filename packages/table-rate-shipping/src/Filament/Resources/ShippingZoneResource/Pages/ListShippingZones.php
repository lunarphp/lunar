<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource;

class ListShippingZones extends BaseListRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->form([
                ShippingZoneResource::getNameFormComponent(),
                ShippingZoneResource::getTypeFormComponent(),
            ]),
        ];
    }
}

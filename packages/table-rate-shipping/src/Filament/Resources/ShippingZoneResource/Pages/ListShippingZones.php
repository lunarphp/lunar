<?php

namespace Lunar\Shipping\Filament\Resources\ShippingZoneResource\Pages;

use Filament\Actions\CreateAction;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource;
use Lunar\Shipping\Filament\Resources\ShippingZoneResource\Schemas\ShippingZoneForm;

class ListShippingZones extends BaseListRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make()->form([
                ShippingZoneForm::getNameComponent(),
                ShippingZoneForm::getTypeComponent(),
            ]),
        ];
    }
}

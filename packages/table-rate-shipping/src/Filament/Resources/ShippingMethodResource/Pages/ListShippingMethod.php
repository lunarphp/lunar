<?php

namespace Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Group;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource;

class ListShippingMethod extends BaseListRecords
{
    protected static string $resource = ShippingMethodResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->form([
                ShippingMethodResource::getNameFormComponent(),
                Group::make([
                    ShippingMethodResource::getCodeFormComponent(),
                    ShippingMethodResource::getDriverFormComponent(),
                ])->columns(2),
                ShippingMethodResource::getDescriptionFormComponent(),
            ]),
        ];
    }
}

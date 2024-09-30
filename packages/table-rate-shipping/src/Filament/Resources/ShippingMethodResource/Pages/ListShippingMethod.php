<?php

namespace Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Group;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Models\CustomerGroup;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource;
use Lunar\Shipping\Models\ShippingMethod;

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
            ])->after(function (ShippingMethod $shippingMethod) {
                $customerGroups = CustomerGroup::pluck('id')->mapWithKeys(
                    fn ($id) => [$id => ['visible' => true, 'enabled' => true, 'starts_at' => now()]]
                );
                $shippingMethod->customerGroups()->sync($customerGroups);
            }),
        ];
    }
}

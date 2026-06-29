<?php

namespace Lunar\Shipping\Filament\Resources\ShippingMethodResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Group;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource;
use Lunar\Shipping\Filament\Resources\ShippingMethodResource\Schemas\ShippingMethodForm;
use Lunar\Shipping\Models\ShippingMethod;

class ListShippingMethod extends BaseListRecords
{
    protected static string $resource = ShippingMethodResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make()->form([
                ShippingMethodForm::getNameComponent(),
                Group::make([
                    ShippingMethodForm::getCodeComponent(),
                    ShippingMethodForm::getDriverComponent(),
                    ShippingMethodForm::getChargeByComponent(),
                ])->columns(2),
                ShippingMethodForm::getDescriptionComponent(),
            ])->after(function (ShippingMethod $shippingMethod) {
                $customerGroups = CustomerGroup::pluck('id')->mapWithKeys(
                    fn ($id) => [$id => ['visible' => true, 'enabled' => true, 'starts_at' => now()]]
                );
                $shippingMethod->customerGroups()->sync($customerGroups);
            }),
        ];
    }
}

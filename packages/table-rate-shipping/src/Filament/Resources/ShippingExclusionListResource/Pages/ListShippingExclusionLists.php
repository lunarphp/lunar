<?php

namespace Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource;

class ListShippingExclusionLists extends BaseListRecords
{
    protected static string $resource = ShippingExclusionListResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->form([
                ShippingExclusionListResource::getNameFormComponent(),
            ]),
        ];
    }
}

<?php

namespace Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Pages;

use Filament\Actions\CreateAction;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Schemas\ShippingExclusionListForm;

class ListShippingExclusionLists extends BaseListRecords
{
    protected static string $resource = ShippingExclusionListResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make()->form([
                ShippingExclusionListForm::getNameComponent(),
            ]),
        ];
    }
}

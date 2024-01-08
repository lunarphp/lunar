<?php

namespace Lunar\Shipping\Filament\Resources\ShippingRateResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Shipping\Filament\Resources\ShippingRateResource;

class EditShippingRate extends BaseEditRecord
{
    protected static string $resource = ShippingRateResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

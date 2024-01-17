<?php

namespace Lunar\Shipping\Filament\Resources\ShippingExclusionListResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Shipping\Filament\Resources\ShippingExclusionListResource;

class EditShippingExclusionList extends BaseEditRecord
{
    protected static string $resource = ShippingExclusionListResource::class;

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

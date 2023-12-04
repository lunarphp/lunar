<?php

namespace Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\CustomerGroupResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditCustomerGroup extends BaseEditRecord
{
    protected static string $resource = CustomerGroupResource::class;

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

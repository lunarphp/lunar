<?php

namespace Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages;

use Lunar\Admin\Filament\Resources\CustomerGroupResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateCustomerGroup extends BaseCreateRecord
{
    protected static string $resource = CustomerGroupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

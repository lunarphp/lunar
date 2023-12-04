<?php

namespace Lunar\Admin\Filament\Resources\CustomerGroupResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\CustomerGroupResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListCustomerGroups extends BaseListRecords
{
    protected static string $resource = CustomerGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

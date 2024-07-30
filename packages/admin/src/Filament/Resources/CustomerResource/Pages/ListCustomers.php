<?php

namespace Lunar\Admin\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListCustomers extends BaseListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

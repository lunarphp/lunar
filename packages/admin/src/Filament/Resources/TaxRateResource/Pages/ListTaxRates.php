<?php

namespace Lunar\Admin\Filament\Resources\TaxRateResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\TaxRateResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListTaxRates extends BaseListRecords
{
    protected static string $resource = TaxRateResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\TaxRateResource\Pages;

use Lunar\Admin\Filament\Resources\TaxRateResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateTaxRate extends BaseCreateRecord
{
    protected static string $resource = TaxRateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

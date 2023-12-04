<?php

namespace Lunar\Admin\Filament\Resources\TaxZoneResource\Pages;

use Lunar\Admin\Filament\Resources\TaxZoneResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateTaxZone extends BaseCreateRecord
{
    protected static string $resource = TaxZoneResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

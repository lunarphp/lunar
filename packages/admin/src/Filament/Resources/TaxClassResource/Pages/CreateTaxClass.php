<?php

namespace Lunar\Admin\Filament\Resources\TaxClassResource\Pages;

use Lunar\Admin\Filament\Resources\TaxClassResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateTaxClass extends BaseCreateRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\CurrencyResource\Pages;

use Lunar\Admin\Filament\Resources\CurrencyResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateCurrency extends BaseCreateRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\CurrencyResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\CurrencyResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListCurrencies extends BaseListRecords
{
    protected static string $resource = CurrencyResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\CurrencyResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\CurrencyResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditCurrency extends BaseEditRecord
{
    protected static string $resource = CurrencyResource::class;

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

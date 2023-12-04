<?php

namespace Lunar\Admin\Filament\Resources\TaxClassResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\TaxClassResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditTaxClass extends BaseEditRecord
{
    protected static string $resource = TaxClassResource::class;

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

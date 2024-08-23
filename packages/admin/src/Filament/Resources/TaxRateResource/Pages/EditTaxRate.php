<?php

namespace Lunar\Admin\Filament\Resources\TaxRateResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\TaxRateResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditTaxRate extends BaseEditRecord
{
    protected static string $resource = TaxRateResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            TaxRateResource\RelationManagers\TaxRateAmountRelationManager::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

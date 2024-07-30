<?php

namespace Lunar\Admin\Filament\Resources\TaxClassResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\TaxClassResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListTaxClasses extends BaseListRecords
{
    protected static string $resource = TaxClassResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

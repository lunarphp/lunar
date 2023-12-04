<?php

namespace Lunar\Admin\Filament\Resources\ProductTypeResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProductType extends BaseEditRecord
{
    protected static string $resource = ProductTypeResource::class;

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

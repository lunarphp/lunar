<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditBrand extends BaseEditRecord
{
    protected static string $resource = BrandResource::class;

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

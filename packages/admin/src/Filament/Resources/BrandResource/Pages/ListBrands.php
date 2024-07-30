<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListBrands extends BaseListRecords
{
    protected static string $resource = BrandResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

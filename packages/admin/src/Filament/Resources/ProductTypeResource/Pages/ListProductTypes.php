<?php

namespace Lunar\Admin\Filament\Resources\ProductTypeResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListProductTypes extends BaseListRecords
{
    protected static string $resource = ProductTypeResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

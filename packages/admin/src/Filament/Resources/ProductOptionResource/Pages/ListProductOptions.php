<?php

namespace Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListProductOptions extends BaseListRecords
{
    protected static string $resource = ProductOptionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;

use Filament\Actions;
use Lunar\Admin\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProductOption extends BaseEditRecord
{
    protected static string $resource = ProductOptionResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\ProductOptionResource\Pages;

use Lunar\Admin\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateProductOption extends BaseCreateRecord
{
    protected static string $resource = ProductOptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['shared'] = true;
    
        return $data;
    }
}

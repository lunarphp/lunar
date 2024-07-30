<?php

namespace Lunar\Admin\Filament\Resources\ProductTypeResource\Pages;

use Lunar\Admin\Filament\Resources\ProductTypeResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateProductType extends BaseCreateRecord
{
    protected static string $resource = ProductTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

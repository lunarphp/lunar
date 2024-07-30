<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateBrand extends BaseCreateRecord
{
    protected static string $resource = BrandResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace Lunar\Admin\Filament\Resources\ProductVariantResource\Pages;

use Lunar\Admin\Filament\Resources\ProductVariantResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListProductVariants extends BaseListRecords
{
    protected static string $resource = ProductVariantResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [];
    }

    public static function createActionFormInputs(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [];
    }
}

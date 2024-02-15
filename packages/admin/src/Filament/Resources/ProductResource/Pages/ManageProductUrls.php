<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Resources\Pages\ManageUrlsRelatedRecords;
use Lunar\Models\Product;

class ManageProductUrls extends ManageUrlsRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $model = Product::class;
}

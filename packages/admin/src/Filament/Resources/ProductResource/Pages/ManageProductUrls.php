<?php

namespace Lunar\Admin\Filament\Resources\ProductResource\Pages;

use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Resources\Pages\ManageUrlsRelatedRecords;
use Lunar\Models\Contracts\Product as ProductContract;

class ManageProductUrls extends ManageUrlsRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $model = ProductContract::class;
}

<?php

namespace Lunar\Admin\Filament\Resources\BrandResource\Pages;

use Lunar\Admin\Filament\Resources\BrandResource;
use Lunar\Admin\Support\Resources\Pages\ManageUrlsRelatedRecords;
use Lunar\Models\Brand;

class ManageBrandUrls extends ManageUrlsRelatedRecords
{
    protected static string $resource = BrandResource::class;

    protected static string $model = Brand::class;
}

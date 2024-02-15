<?php

namespace Lunar\Admin\Filament\Resources\CollectionResource\Pages;

use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Support\Resources\Pages\ManageUrlsRelatedRecords;
use Lunar\Models\Collection;

class ManageCollectionUrls extends ManageUrlsRelatedRecords
{
    protected static string $resource = CollectionResource::class;

    protected static string $model = Collection::class;
}

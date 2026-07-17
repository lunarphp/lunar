<?php

namespace Lunar\Admin\Support;

use Lunar\Admin\Filament\Resources\CollectionResource;
use Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder;
use Lunar\Admin\Filament\Resources\ProductVariantResource;

class RecordUrlResolvers
{
    public static function order(mixed $record, array $context = []): string
    {
        return ManageOrder::getUrl([...$context, 'record' => $record]);
    }

    public static function productVariant(mixed $record, array $context = []): string
    {
        return ProductVariantResource::getUrl('edit', [...$context, 'record' => $record]);
    }

    public static function collectionEdit(mixed $record, array $context = []): string
    {
        return CollectionResource::getUrl('edit', [...$context, 'record' => $record]);
    }
}

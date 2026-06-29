<?php

namespace Lunar\Filament\GlobalSearch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\Product;

/**
 * @extends GlobalSearchDescriptor<Product>
 */
class ProductGlobalSearch extends GlobalSearchDescriptor
{
    public static function getModelContract(): string
    {
        return Product::class;
    }

    public static function getSearchableAttributes(): array
    {
        return [
            'variants.sku',
            'tags.value',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'variants',
            'brand',
            'tags',
        ]);
    }

    public static function getResultDetails(Model $record): array
    {
        /** @var Product $record */
        $variant = $record->variants->first();

        return [
            __('lunar-filament::global-search.products.details.sku') => $variant?->getIdentifier(),
            __('lunar-filament::global-search.products.details.status') => $record->status->label(),
            __('lunar-filament::global-search.products.details.brand') => $record->brand?->name,
        ];
    }
}

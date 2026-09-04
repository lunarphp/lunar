<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Core\Models\ProductVariant;

class ProductVariantResource extends Resource
{
    public static function type(): string
    {
        return 'variants';
    }

    public static function model(): string
    {
        return ProductVariant::class;
    }

    public function fields(): array
    {
        return [
            Field::make('sku'),
            Field::make('gtin'),
            Field::make('mpn'),
            Field::make('ean'),
            Field::make('unit_quantity'),
            Field::make('min_quantity'),
            Field::make('quantity_increment'),
            Field::make('shippable'),
            Field::make('selling_policy'),
            Field::make('stock', fn (ProductVariant $variant) => (int) $variant->stock_available),
            Field::make('purchasable', fn (ProductVariant $variant) => $variant->isPurchasable()),
            Field::make('price', fn (ProductVariant $variant, SerializationContext $context) => ProductResource::variantPrice($variant, $context))
                ->eagerLoad(['prices.currency', 'prices.priceable']),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('product', ProductResource::class),
            Embed::relation('values', ProductOptionValueResource::class),
        ];
    }

    public function query(SerializationContext $context): Builder
    {
        return ProductVariant::query()
            ->where('enabled', true)
            ->whereHas('product', fn (Builder $product) => ProductResource::visible($product, $context));
    }
}

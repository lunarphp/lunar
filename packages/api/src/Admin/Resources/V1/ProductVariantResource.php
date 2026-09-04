<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\Resources\Embed;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
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
            Field::make('enabled'),
            Field::make('unit_quantity'),
            Field::make('min_quantity'),
            Field::make('quantity_increment'),
            Field::make('shippable'),
            Field::make('selling_policy'),
            Field::make('tax_ref'),
            Field::make('stock_on_hand'),
            Field::make('stock_available'),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('product', ProductResource::class),
            Embed::relation('prices', PriceResource::class),
        ];
    }
}

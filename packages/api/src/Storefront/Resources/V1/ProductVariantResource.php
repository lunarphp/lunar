<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'A sellable variant of a product with its stock and price for the request.';
    }

    public function fields(): array
    {
        return [
            Field::make('sku')->describe('Stock keeping unit.'),
            Field::make('gtin')->nullable()->describe('Global Trade Item Number.'),
            Field::make('mpn')->nullable()->describe('Manufacturer Part Number.'),
            Field::make('ean')->nullable()->describe('European Article Number.'),
            Field::make('unit_quantity')->type(Schema::integer())->describe('Units in one sellable quantity.'),
            Field::make('min_quantity')->type(Schema::integer())->describe('Smallest quantity that can be added to a cart.'),
            Field::make('quantity_increment')->type(Schema::integer())->describe('Step between allowed quantities.'),
            Field::make('shippable')->describe('Whether the variant needs shipping.'),
            Field::make('selling_policy')->describe('How the variant sells relative to its stock.'),
            Field::make('stock', fn (ProductVariant $variant) => (int) $variant->stock_available)
                ->type(Schema::integer())->describe('Units available to sell.'),
            Field::make('purchasable', fn (ProductVariant $variant) => $variant->isPurchasable())
                ->type(Schema::boolean())->describe('Whether the variant can be added to a cart right now.'),
            Field::make('price', fn (ProductVariant $variant, SerializationContext $context) => ProductResource::variantPrice($variant, $context))
                ->eagerLoad(['prices.currency', 'prices.priceable'])
                ->type(Schema::money()->nullable())->describe('The single-unit price for the request currency and customer groups, or null when unpriced.'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('product', ProductResource::class)->describe('The parent product.'),
            Embed::relation('values', ProductOptionValueResource::class)->describe('The option values that distinguish this variant.'),
        ];
    }

    public function query(SerializationContext $context): Builder
    {
        return ProductVariant::query()
            ->where('enabled', true)
            ->whereHas('product', fn (Builder $product) => ProductResource::visible($product, $context));
    }
}

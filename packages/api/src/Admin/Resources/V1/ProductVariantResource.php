<?php

namespace Lunar\Api\Admin\Resources\V1;

use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'A variant of a product with its stock levels and prices.';
    }

    public function fields(): array
    {
        return [
            Field::make('sku')->describe('Stock keeping unit.'),
            Field::make('gtin')->nullable()->describe('Global Trade Item Number.'),
            Field::make('mpn')->nullable()->describe('Manufacturer Part Number.'),
            Field::make('ean')->nullable()->describe('European Article Number.'),
            Field::make('enabled')->describe('Whether the variant is offered for sale.'),
            Field::make('unit_quantity')->type(Schema::integer())->describe('Units in one sellable quantity.'),
            Field::make('min_quantity')->type(Schema::integer())->describe('Smallest quantity that can be added to a cart.'),
            Field::make('quantity_increment')->type(Schema::integer())->describe('Step between allowed quantities.'),
            Field::make('shippable')->describe('Whether the variant needs shipping.'),
            Field::make('selling_policy')->describe('How the variant sells relative to its stock.'),
            Field::make('tax_ref')->nullable()->describe('Reference the tax driver uses to classify the variant.'),
            Field::make('stock_on_hand')->describe('Units physically in stock.'),
            Field::make('stock_available')->describe('Units available to sell after commitments and reservations.'),
            Field::make('created_at')->describe('When the variant was created.'),
            Field::make('updated_at')->describe('When the variant was last updated.'),
        ];
    }

    public function includes(): array
    {
        return [
            Embed::relation('product', ProductResource::class)->describe('The parent product.'),
            Embed::relation('prices', PriceResource::class)->describe('Every price of the variant.'),
        ];
    }
}

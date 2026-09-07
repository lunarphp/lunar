<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\OpenApi\Schema;
use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
use Lunar\Core\Models\CartLine;

class CartLineResource extends Resource
{
    public static function type(): string
    {
        return 'cart-lines';
    }

    public static function model(): string
    {
        return CartLine::class;
    }

    public static function description(): string
    {
        return 'A purchasable and quantity on a cart, with its calculated totals. Lines are embedded in carts and have no endpoints of their own.';
    }

    public function fields(): array
    {
        return [
            Field::make('quantity')->describe('Units of the purchasable on the line.'),
            Field::make('purchasable_type')->describe('The purchasable morph alias, for example product_variant.'),
            Field::make('purchasable_id', fn (CartLine $line) => $line->purchasable?->public_id)
                ->type(Schema::string()->nullable())->describe('Public id of the purchasable.'),
            Field::make('identifier', fn (CartLine $line) => $line->purchasable?->getIdentifier())
                ->type(Schema::string()->nullable())->describe('The purchasable identifier, such as the variant SKU.'),
            Field::make('description', fn (CartLine $line) => $line->purchasable?->getDescription())
                ->type(Schema::string()->nullable())->describe('The purchasable description, such as the product name.'),
            Field::make('unit_price', fn (CartLine $line) => $line->unitPrice)->type(Schema::money())->describe('Price of one unit before discounts.'),
            Field::make('sub_total', fn (CartLine $line) => $line->subTotal)->type(Schema::money())->describe('Unit price times quantity, before discounts and tax.'),
            Field::make('discount_total', fn (CartLine $line) => $line->discountTotal)->type(Schema::money())->describe('Discount applied to the line.'),
            Field::make('tax_total', fn (CartLine $line) => $line->taxAmount)->type(Schema::money())->describe('Tax on the line.'),
            Field::make('total', fn (CartLine $line) => $line->total)->type(Schema::money())->describe('Line total after discounts and tax.'),
            Field::make('meta')->nullable()->describe('Arbitrary metadata attached when the line was added.'),
        ];
    }
}

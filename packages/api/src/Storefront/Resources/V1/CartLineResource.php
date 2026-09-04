<?php

namespace Lunar\Api\Storefront\Resources\V1;

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

    public function fields(): array
    {
        return [
            Field::make('quantity'),
            Field::make('purchasable_type'),
            Field::make('purchasable_id', fn (CartLine $line) => $line->purchasable?->public_id),
            Field::make('identifier', fn (CartLine $line) => $line->purchasable?->getIdentifier()),
            Field::make('description', fn (CartLine $line) => $line->purchasable?->getDescription()),
            Field::make('unit_price', fn (CartLine $line) => $line->unitPrice),
            Field::make('sub_total', fn (CartLine $line) => $line->subTotal),
            Field::make('discount_total', fn (CartLine $line) => $line->discountTotal),
            Field::make('tax_total', fn (CartLine $line) => $line->taxAmount),
            Field::make('total', fn (CartLine $line) => $line->total),
            Field::make('meta'),
        ];
    }
}

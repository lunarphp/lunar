<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\Resources\Field;
use Lunar\Api\Resources\Resource;
use Lunar\Api\Resources\SerializationContext;
use Lunar\Core\Models\Cart;

/**
 * The calculated cart. Lines embed outright rather than as an include: a cart
 * without its lines is not useful to any consumer.
 */
class CartResource extends Resource
{
    public static function type(): string
    {
        return 'carts';
    }

    public static function model(): string
    {
        return Cart::class;
    }

    public function fields(): array
    {
        return [
            Field::make('currency', fn (Cart $cart) => $cart->currency?->code),
            Field::make('channel', fn (Cart $cart) => $cart->channel?->handle),
            Field::make('coupon_code'),
            Field::make('lines', fn (Cart $cart, SerializationContext $context) => $context->serialize(CartLineResource::class, $cart->lines)),
            Field::make('sub_total', fn (Cart $cart) => $cart->subTotal),
            Field::make('discount_total', fn (Cart $cart) => $cart->discountTotal),
            Field::make('shipping_total', fn (Cart $cart) => $cart->shippingTotal),
            Field::make('tax_total', fn (Cart $cart) => $cart->taxTotal),
            Field::make('total', fn (Cart $cart) => $cart->total),
            Field::make('created_at'),
            Field::make('updated_at'),
        ];
    }

    public function eagerLoad(): array
    {
        return ['currency', 'channel', 'lines.purchasable'];
    }
}

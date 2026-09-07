<?php

namespace Lunar\Api\Storefront\Resources\V1;

use Lunar\Api\OpenApi\Schema;
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

    public static function description(): string
    {
        return 'The calculated cart for the request. Carts are addressed by the signed X-Lunar-Cart token, not by id.';
    }

    public function fields(): array
    {
        return [
            Field::make('currency', fn (Cart $cart) => $cart->currency?->code)
                ->type(Schema::string()->nullable())->describe('ISO 4217 code of the cart currency.'),
            Field::make('channel', fn (Cart $cart) => $cart->channel?->handle)
                ->type(Schema::string()->nullable())->describe('Handle of the channel the cart belongs to.'),
            Field::make('coupon_code')->nullable()->describe('The coupon code applied to the cart, if any.'),
            Field::make('lines', fn (Cart $cart, SerializationContext $context) => $context->serialize(CartLineResource::class, $cart->lines))
                ->type(Schema::array(Schema::ref(CartLineResource::class)))->describe('The cart lines, always embedded.'),
            Field::make('sub_total', fn (Cart $cart) => $cart->subTotal)->type(Schema::money())->describe('Sum of line sub totals, before discounts and tax.'),
            Field::make('discount_total', fn (Cart $cart) => $cart->discountTotal)->type(Schema::money())->describe('Total discount across the cart.'),
            Field::make('shipping_total', fn (Cart $cart) => $cart->shippingTotal)->type(Schema::money())->describe('Shipping cost, including tax.'),
            Field::make('tax_total', fn (Cart $cart) => $cart->taxTotal)->type(Schema::money())->describe('Total tax across the cart.'),
            Field::make('total', fn (Cart $cart) => $cart->total)->type(Schema::money())->describe('Amount payable.'),
            Field::make('created_at')->describe('When the cart was created.'),
            Field::make('updated_at')->describe('When the cart was last changed.'),
        ];
    }

    public function eagerLoad(): array
    {
        return ['currency', 'channel', 'lines.purchasable'];
    }
}

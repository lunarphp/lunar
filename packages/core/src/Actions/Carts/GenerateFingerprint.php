<?php

namespace Lunar\Actions\Carts;

use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\CartLine as CartLineContract;

class GenerateFingerprint
{
    public function execute(CartContract $cart)
    {
        /** @var Cart $cart */
        $value = $cart->lines->reduce(function (?string $carry, CartLineContract $line) {
            /** @var CartLine $line */
            return $carry.
                $line->purchasable_type.
                $line->purchasable_id.
                $line->quantity.
                $line->subTotal;
        });

        $value .= $cart->user_id.$cart->currency_id.$cart->coupon_code;

        return sha1($value);
    }
}

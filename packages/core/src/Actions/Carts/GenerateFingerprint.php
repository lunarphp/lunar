<?php

namespace Lunar\Actions\Carts;

use Lunar\Models\Cart;
use Lunar\Models\CartLine;

class GenerateFingerprint
{
    public function execute(Cart $cart)
    {
        $value = $cart->lines->reduce(function (?string $carry, CartLine $line) {
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

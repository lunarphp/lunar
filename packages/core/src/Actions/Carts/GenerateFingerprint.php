<?php

namespace Lunar\Actions\Carts;

use Lunar\Models\Cart;
use Lunar\Models\CartLine;

class GenerateFingerprint
{
    public function execute(Cart $cart)
    {
        $value = $cart->lines->reduce(function (?string $carry, CartLine $line) {
            $meta = $line->meta?->collect()->sortKeys()->toJson();

            return $carry.
                $line->purchasable_type.
                $line->purchasable_id.
                $line->quantity.
                $meta.
                $line->subTotal;
        });

        $value .= $cart->user_id.$cart->currency_id.$cart->coupon_code;
        $value .= $cart->meta?->collect()->sortKeys()->toJson();

        return sha1($value);
    }
}

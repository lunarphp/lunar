<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\GeneratesFingerprint;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;

class GenerateFingerprint implements GeneratesFingerprint
{
    public function execute(Cart $cart): string
    {
        /** @var Cart $cart */
        $value = $cart->lines->reduce(function (?string $carry, CartLine $line) {
            /** @var CartLine $line */
            return $carry.
                $line->purchasable_type.
                $line->purchasable_id.
                $line->quantity.
                $line->fulfilment_method.
                $line->subTotal?->value;
        });

        $value .= $cart->user_id.$cart->currency_id.$cart->coupon_code.$cart->fulfilment_method.$cart->pickup_location_id;

        return sha1($value);
    }
}

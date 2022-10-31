<?php

namespace Lunar\Validation\CartLine;

use Lunar\Base\Purchasable;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Validation\BaseValidator;

class CartLineQuantity extends BaseValidator
{
    public function validate(Cart $cart, Purchasable $purchasable, $quantity, $meta = [])
    {
        if ($quantity < 1) {
            $this->fail(
                'cart',
                __('lunar::exceptions.invalid_cart_line_quantity', [
                    'quantity' => $quantity,
                ])
            );
        }

        if ($quantity > 1000000) {
            $this->fail(
                'cart',
                __('lunar::exceptions.maximum_cart_line_quantity', [
                    'quantity' => 1000000,
                ])
            );
        }

        return true;
    }
}

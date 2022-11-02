<?php

namespace Lunar\Validation\Cart;

use Lunar\Validation\BaseValidator;

class ShippingOptionValidator extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        $cart = $this->parameters['cart'] ?? null;

        if (!$cart) {
            return $this->fail('cart', 'Unable to set shipping option on null');
        }

        if (! $cart->shippingAddress) {
            return $this->fail('cart', 'No shipping address on cart');
        }

        return $this->pass();
    }
}

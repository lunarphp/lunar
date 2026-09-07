<?php

namespace Lunar\Checkout\Validation\Cart;

use Lunar\Checkout\Support\PickupPoints;
use Lunar\Core\Models\Cart;
use Lunar\Core\Validation\BaseValidator;

/**
 * Lunar's order-creation validator for collect orders (spec 0013 §D): a
 * point is required when the host offers any, and the recorded mode must
 * agree with the stored option. Runs after core's ValidateCartForOrderCreation.
 */
class PickupPointRequired extends BaseValidator
{
    public function validate(): bool
    {
        /** @var Cart|null $cart */
        $cart = $this->parameters['cart'] ?? null;

        if (! $cart instanceof Cart) {
            return $this->pass();
        }

        $mode = $cart->meta[PickupPoints::MODE_KEY] ?? null;
        $collects = PickupPoints::storedOptionCollects($cart);

        if ($mode !== null && (($mode === PickupPoints::COLLECT) !== $collects)) {
            return $this->fail('fulfilment', 'The fulfilment mode and the shipping option disagree.');
        }

        if (PickupPoints::missing($cart)) {
            return $this->fail('pickup_point', 'Choose where you would like to collect your order.');
        }

        return $this->pass();
    }
}

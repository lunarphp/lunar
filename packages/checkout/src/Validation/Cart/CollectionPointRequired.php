<?php

namespace Lunar\Checkout\Validation\Cart;

use Lunar\Checkout\Support\CollectionPoints;
use Lunar\Core\Models\Cart;
use Lunar\Core\Validation\BaseValidator;

/**
 * Lunar's order-creation validator for collect orders (spec 0013 §D): a
 * point is required when the host offers any, and the recorded mode must
 * agree with the stored option. Runs after core's ValidateCartForOrderCreation.
 */
class CollectionPointRequired extends BaseValidator
{
    public function validate(): bool
    {
        /** @var Cart|null $cart */
        $cart = $this->parameters['cart'] ?? null;

        if (! $cart instanceof Cart) {
            return $this->pass();
        }

        $mode = $cart->meta[CollectionPoints::MODE_KEY] ?? null;
        $collects = CollectionPoints::storedOptionCollects($cart);

        if ($mode !== null && (($mode === CollectionPoints::COLLECT) !== $collects)) {
            return $this->fail('fulfilment', 'The fulfilment mode and the shipping option disagree.');
        }

        if (CollectionPoints::missing($cart)) {
            return $this->fail('collection_point', 'Choose where you would like to collect your order.');
        }

        return $this->pass();
    }
}

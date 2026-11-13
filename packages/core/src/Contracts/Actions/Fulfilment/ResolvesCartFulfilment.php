<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Cart;
use Lunar\Core\ValueObjects\Cart\CartFulfilment;

interface ResolvesCartFulfilment
{
    /**
     * Resolve how the cart and each of its lines will be fulfilled. Without
     * the shipping option, only explicit choices and restricted items count.
     */
    public function execute(Cart $cart, bool $withShippingOption = true): CartFulfilment;
}

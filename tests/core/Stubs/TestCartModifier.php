<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;
use Lunar\Core\Modifiers\CartModifier;

class TestCartModifier extends CartModifier
{
    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(Cart $cart, Closure $next): Cart
    {
        $cart->total = new PriceValue(5000, $cart->currency);

        return $next($cart);
    }
}

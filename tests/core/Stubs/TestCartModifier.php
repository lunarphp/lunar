<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Base\CartModifier;
use Lunar\Models\Cart;

class TestCartModifier extends CartModifier
{
    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(Cart $cart, Closure $next): Cart
    {
        $cart->total->value = 5000;

        return $next($cart);
    }
}

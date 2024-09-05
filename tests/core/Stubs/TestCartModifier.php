<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Base\CartModifier;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;

class TestCartModifier extends CartModifier
{
    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(CartContract $cart, Closure $next): CartContract
    {
        $cart->total->value = 5000;

        return $next($cart);
    }
}

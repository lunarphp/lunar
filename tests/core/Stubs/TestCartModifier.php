<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\Base\CartModifier;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;

class TestCartModifier extends CartModifier
{
    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(CartContract $cart, Closure $next): CartContract
    {
        $cart->total = new PriceValue(5000, $cart->currency);

        return $next($cart);
    }
}

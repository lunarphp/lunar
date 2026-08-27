<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\Models\Cart;
use Lunar\Core\Modifiers\ShippingModifier;

class TestRecursiveShippingModifier extends ShippingModifier
{
    /**
     * The number of times this modifier has been run.
     */
    public static int $calls = 0;

    /**
     * A self-imposed ceiling so an unguarded re-entry fails the test
     * rather than exhausting the stack.
     */
    public static int $limit = 25;

    public function handle(Cart $cart, Closure $next)
    {
        static::$calls++;

        if (static::$calls < static::$limit) {
            $cart->calculate();
        }

        return $next($cart);
    }
}

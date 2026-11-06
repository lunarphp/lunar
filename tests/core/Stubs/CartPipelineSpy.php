<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\Models\Cart;

/**
 * Prepended to the cart calculation steps to count pipeline runs, so a test
 * can assert that a calculate() was served from the persisted snapshot.
 */
class CartPipelineSpy
{
    public static int $runs = 0;

    public static function reset(): void
    {
        static::$runs = 0;
    }

    public function handle(Cart $cart, Closure $next): mixed
    {
        static::$runs++;

        return $next($cart);
    }
}

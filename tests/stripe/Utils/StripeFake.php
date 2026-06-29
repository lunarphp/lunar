<?php

namespace Lunar\Tests\Stripe\Utils;

use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\MockClient;

class StripeFake
{
    /**
     * Fake the Stripe client and return a MockClient pre-loaded with the cart's
     * amount and currency so subsequent payment intent responses match by default.
     */
    public static function forCart(Cart $cart, array $extra = []): MockClient
    {
        $cart->calculate();

        return Stripe::fake([
            'amount' => $cart->total->value,
            'currency' => strtolower($cart->currency->code),
            ...$extra,
        ]);
    }

    /**
     * Fake the Stripe client and return a MockClient pre-loaded with the order's
     * amount and currency.
     */
    public static function forOrder(Order $order, array $extra = []): MockClient
    {
        return Stripe::fake([
            'amount' => $order->total,
            'currency' => strtolower($order->currency_code),
            ...$extra,
        ]);
    }
}

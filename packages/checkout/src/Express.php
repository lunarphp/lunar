<?php

namespace Lunar\Checkout;

use Lunar\Checkout\Contracts\PaymentMethod;
use Lunar\Checkout\Contracts\PaymentMethodRegistry;
use Lunar\Core\Contracts\SupportsPaymentHolds;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Cart;
use Throwable;

/**
 * Host-facing express projection (spec 0012 SF): the express-eligible
 * methods plus client config, in the same shape as the checkout page's
 * paymentMethods projection, for host pages (cart, later a mini-cart)
 * that are not the checkout bundle.
 */
class Express
{
    /**
     * `payable` says whether ANY registered method can serve this basket, and
     * `unavailable` carries the customer-facing reasons the withdrawn methods
     * gave (spec 0002 §B), so the host can say why the checkout is closed
     * before the customer walks into an empty payment region.
     *
     * @return array{methods: array<int, array<string, mixed>>, payable: bool, unavailable: array<int, string>}
     */
    public static function projection(Cart $cart): array
    {
        $registry = app(PaymentMethodRegistry::class);
        $available = $registry->availableFor($cart);

        $methods = collect($available)
            ->filter(fn (PaymentMethod $method): bool => $method->supportsExpress()
                && $method->expressComponent() !== null
                && static::driverSupportsHolds($method))
            ->map(fn (PaymentMethod $method): array => [
                'handle' => $method->handle(),
                'label' => $method->label(),
                'driver' => $method->driver(),
                'component' => $method->component(),
                'expressComponent' => $method->expressComponent(),
                'supportsExpress' => true,
                'requiresIntent' => $method->requiresIntent(),
                'config' => $method->config(),
            ])
            ->values()
            ->all();

        return [
            'methods' => $methods,
            'payable' => $available !== [],
            'unavailable' => array_values($registry->unavailableReasons($cart)),
        ];
    }

    /**
     * Whether the method's registered driver can actually authorise a hold.
     * A method's own `supportsExpress()` is a claim, never proof: the wallet
     * flow needs a hold-capable gateway underneath it, so this is checked
     * server-side wherever `supportsExpress` is projected (here and in
     * `CheckoutController::projectCheckout()`).
     *
     * `Payments::driver()` throws for a driver key nothing has registered
     * (a method left pointing at a removed or misconfigured gateway); that
     * must read as "not express-capable", never surface as a 500.
     */
    public static function driverSupportsHolds(PaymentMethod $method): bool
    {
        try {
            return Payments::driver($method->driver()) instanceof SupportsPaymentHolds;
        } catch (Throwable) {
            return false;
        }
    }
}

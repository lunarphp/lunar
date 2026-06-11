<?php

namespace Lunar\Checkout\Actions;

use Lunar\Checkout\Contracts\Actions\CreatesCheckoutSession;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\Models\Cart;

/**
 * The default `LunarCheckoutDriver` ingest path (spec 0004 §B). Snapshots the
 * cart's computed totals and pins channel/currency/locale from the storefront
 * session — the integrity anchor the driver later verifies the gateway intent
 * against. The shopper's editable cart is untouched.
 */
final class CreateCheckoutSession implements CreatesCheckoutSession
{
    public function __construct(
        private StorefrontSession $storefrontSession,
    ) {}

    public function execute(Cart $cart, array $attributes = []): CheckoutSession
    {
        // The pinned totals come from the cart's computed values, so make sure
        // it is calculated before we freeze the snapshot.
        if (! $cart->isCalculated()) {
            $cart->calculate();
        }

        $window = (int) config('lunar.checkout.session.expires_after', 24);

        return CheckoutSession::create([
            'cart_id' => $cart->id,
            'channel_id' => $this->storefrontSession->getChannel()->id,
            'currency_code' => $this->storefrontSession->getCurrency()->code,
            'locale' => app()->getLocale(),
            'amount_subtotal' => $cart->subTotal?->value ?? 0,
            'amount_total' => $cart->total?->value ?? 0,
            'status' => Open::$name,
            'customer_id' => $this->storefrontSession->getCustomer()?->id,
            'customer_email' => $attributes['customer_email'] ?? null,
            'client_reference_id' => $attributes['client_reference_id'] ?? null,
            'success_url' => $attributes['success_url'] ?? null,
            'cancel_url' => $attributes['cancel_url'] ?? null,
            'metadata' => $attributes['metadata'] ?? null,
            'expires_at' => now()->addHours($window),
        ]);
    }
}

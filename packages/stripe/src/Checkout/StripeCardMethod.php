<?php

namespace Lunar\Stripe\Checkout;

use Lunar\Checkout\Contracts\ExplainsUnavailability;
use Lunar\Checkout\PaymentMethods\AbstractPaymentMethod;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;

/**
 * Card payments through Stripe, as a checkout payment method (spec 0002).
 * Registered by the host app — installing lunar/stripe alone enables nothing;
 * lunar/checkout is a suggested dependency, referenced only when a host that
 * has both registers this class.
 */
class StripeCardMethod extends AbstractPaymentMethod implements ExplainsUnavailability
{
    /**
     * Stripe's minimum charge per settlement currency, in minor units. Anything
     * below is refused at intent creation, so the method withdraws itself first
     * rather than let the customer meet a gateway error at Pay. Overridable per
     * currency through `lunar.stripe.minimum_amounts`.
     *
     * @see https://docs.stripe.com/currencies#minimum-and-maximum-charge-amounts
     */
    public const MINIMUM_AMOUNTS = [
        'USD' => 50,
        'AED' => 200,
        'AUD' => 50,
        'BGN' => 100,
        'BRL' => 50,
        'CAD' => 50,
        'CHF' => 50,
        'CZK' => 1500,
        'DKK' => 250,
        'EUR' => 50,
        'GBP' => 30,
        'HKD' => 400,
        'HUF' => 17500,
        'INR' => 50,
        'JPY' => 50,
        'MXN' => 1000,
        'MYR' => 200,
        'NOK' => 300,
        'NZD' => 50,
        'PLN' => 200,
        'RON' => 200,
        'SEK' => 300,
        'SGD' => 50,
        'THB' => 1000,
    ];

    public function handle(): string
    {
        return 'card';
    }

    public function label(): string
    {
        return 'Card';
    }

    public function driver(): string
    {
        return 'stripe';
    }

    public function component(): string
    {
        return 'stripe-card';
    }

    public function config(): array
    {
        return [
            'publishableKey' => (string) config('services.stripe.public_key'),
        ];
    }

    /**
     * A basket Stripe would refuse to charge is not offered card payment at all
     * (spec 0002 §B). A zero total is left alone: nothing is charged and the
     * checkout's own zero-total handling applies.
     */
    public function isAvailable(Cart $cart): bool
    {
        $total = $this->total($cart);

        return $total === 0 || $total >= $this->minimumAmount($cart);
    }

    public function unavailableReason(Cart $cart): ?string
    {
        if ($this->isAvailable($cart)) {
            return null;
        }

        $minimum = (new PriceValue($this->minimumAmount($cart), $cart->currency))->format();

        return "Card payments need an order total of at least {$minimum}.";
    }

    /**
     * Same method, second placement (spec 0002 SC / 0012 SC): the card tab
     * stays, and the express region renders Stripe's Express Checkout
     * Element. Which wallets appear inside it is Stripe dashboard config.
     */
    public function supportsExpress(): bool
    {
        return true;
    }

    public function expressComponent(): ?string
    {
        return 'stripe-express';
    }

    public function minimumAmount(Cart $cart): int
    {
        $code = strtoupper((string) $cart->currency->code);

        /** @var array<string, int> $overrides */
        $overrides = (array) config('lunar.stripe.minimum_amounts', []);

        return (int) ($overrides[$code] ?? static::MINIMUM_AMOUNTS[$code] ?? 50);
    }

    private function total(Cart $cart): int
    {
        if ($cart->total === null) {
            $cart->calculate();
        }

        return (int) ($cart->total?->value ?? 0);
    }
}

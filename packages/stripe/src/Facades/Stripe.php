<?php

namespace Lunar\Stripe\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Stripe\MockClient;
use Stripe\ApiRequestor;

/**
 * @method static getClient(): \Stripe\StripeClient
 * @method static createIntent(\Lunar\Models\Cart $cart, array $opts): \Stripe\PaymentIntent
 * @method static syncIntent(\Lunar\Models\Cart $cart): void
 * @method static updateIntent(\Lunar\Models\Cart $cart, array $values): void
 * @method static updateShippingAddress(\Lunar\Models\Cart $cart): void
 * @method static getCharges(string $paymentIntentId): \Illuminate\Support\Collection
 * @method static getCharge(string $chargeId): \Stripe\Charge
 * @method static buildIntent(int $value, string $currencyCode, \Lunar\Models\CartAddress $shipping): \Stripe\PaymentIntent
 */
class Stripe extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'lunar:stripe';
    }

    public static function fake(): void
    {
        $mockClient = new MockClient;
        ApiRequestor::setHttpClient($mockClient);
    }
}

<?php

namespace Lunar\Stripe\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Stripe\MockClient;
use Stripe\ApiRequestor;

/**
 * @method static getClient(): \Stripe\StripeClient
 * @method static createIntent(\Lunar\Models\Cart $cart): \Stripe\PaymentIntent
 * @method static syncIntent(\Lunar\Models\Cart $cart): void
 * @method static fetchIntent(string $intentId, $options = null): ?\Stripe\PaymentIntent
 * @method static getCharges(string $paymentIntentId): \Illuminate\Support\Collection
 * @method static getCharge(string $chargeId): \Stripe\Charge
 * @method static buildIntent(int $value, string $currencyCode, \Lunar\Models\CartAddress $shipping): \Stripe\PaymentIntent
 */
class StripeFacade extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'gc:stripe';
    }

    public static function fake(): void
    {
        $mockClient = new MockClient();
        ApiRequestor::setHttpClient($mockClient);
    }
}

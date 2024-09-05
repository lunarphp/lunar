<?php

namespace Lunar\Stripe\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\CartAddress as CartAddressContract;
use Lunar\Stripe\Enums\CancellationReason;
use Lunar\Stripe\MockClient;
use Stripe\ApiRequestor;

/**
 * @method static getClient(): \Stripe\StripeClient
 * @method static getCartIntentId(CartContract $cart): ?string
 * @method static fetchOrCreateIntent(CartContract $cart, array $createOptions): ?string
 * @method static createIntent(CartContract $cart, array $createOptions): \Stripe\PaymentIntent
 * @method static syncIntent(CartContract $cart): void
 * @method static updateIntent(CartContract $cart, array $values): void
 * @method static cancelIntent(CartContract $cart, CancellationReason $reason): void
 * @method static updateShippingAddress(CartContract $cart): void
 * @method static getCharges(string $paymentIntentId): \Illuminate\Support\Collection
 * @method static getCharge(string $chargeId): \Stripe\Charge
 * @method static buildIntent(int $value, string $currencyCode, CartAddressContract $shipping): \Stripe\PaymentIntent
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

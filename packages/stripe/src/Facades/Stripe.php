<?php

namespace Lunar\Stripe\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Stripe\MockClient;
use Stripe\ApiRequestor;

/**
 * @method static \Stripe\StripeClient getClient()
 * @method static string|null getCartIntentId(\Lunar\Models\Contracts\Cart $cart)
 * @method static \Stripe\PaymentIntent fetchOrCreateIntent(\Lunar\Models\Contracts\Cart $cart, array $createOptions = [])
 * @method static \Stripe\PaymentMethod|null getPaymentMethod(string $paymentMethodId)
 * @method static \Stripe\PaymentIntent createIntent(\Lunar\Models\Contracts\Cart $cart, array $opts = [])
 * @method static void updateShippingAddress(\Lunar\Models\Contracts\Cart $cart)
 * @method static void updateIntent(\Lunar\Models\Contracts\Cart $cart, array $values)
 * @method static void updateIntentById(string $id, array $values)
 * @method static void syncIntent(\Lunar\Models\Contracts\Cart $cart)
 * @method static void cancelIntent(\Lunar\Models\Contracts\Cart $cart, \Lunar\Stripe\Enums\CancellationReason $reason)
 * @method static \Stripe\PaymentIntent|null fetchIntent(string $intentId, void $options = null)
 * @method static \Illuminate\Support\Collection getCharges(string $paymentIntentId)
 * @method static \Stripe\Charge getCharge(string $chargeId)
 *
 * @see \Lunar\Stripe\Managers\StripeManager
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

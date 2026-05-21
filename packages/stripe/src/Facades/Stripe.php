<?php

namespace Lunar\Stripe\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Stripe\Managers\StripeManager;
use Lunar\Stripe\MockClient;
use Stripe\ApiRequestor;

/**
 * @method static \Stripe\StripeClient getClient()
 * @method static string|null getCartIntentId(\Lunar\Core\Models\Contracts\Cart $cart)
 * @method static \Stripe\PaymentIntent fetchOrCreateIntent(\Lunar\Core\Models\Contracts\Cart $cart, array $createOptions = [])
 * @method static \Stripe\PaymentMethod|null getPaymentMethod(string $paymentMethodId)
 * @method static \Stripe\PaymentIntent createIntent(\Lunar\Core\Models\Contracts\Cart $cart, array $opts = [])
 * @method static void updateShippingAddress(\Lunar\Core\Models\Contracts\Cart $cart)
 * @method static void updateIntent(\Lunar\Core\Models\Contracts\Cart $cart, array $values)
 * @method static void updateIntentById(string $id, array $values)
 * @method static void syncIntent(\Lunar\Core\Models\Contracts\Cart $cart)
 * @method static void cancelIntent(\Lunar\Core\Models\Contracts\Cart $cart, \Lunar\Stripe\Enums\CancellationReason $reason)
 * @method static \Stripe\PaymentIntent|null fetchIntent(string $intentId, void $options = null)
 * @method static \Illuminate\Support\Collection getCharges(string $paymentIntentId)
 * @method static \Stripe\Charge getCharge(string $chargeId)
 *
 * @see StripeManager
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

    public static function fake(array $data = []): MockClient
    {
        $mockClient = new MockClient;
        $mockClient->next($data);

        ApiRequestor::setHttpClient($mockClient);

        return $mockClient;
    }
}

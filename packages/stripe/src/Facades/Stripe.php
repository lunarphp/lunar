<?php

namespace Lunar\Stripe\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Models\Cart;
use Lunar\Stripe\Enums\CancellationReason;
use Lunar\Stripe\Managers\StripeManager;
use Lunar\Stripe\MockClient;
use Stripe\ApiRequestor;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\StripeClient;

/**
 * @method static StripeClient getClient()
 * @method static string|null getCartIntentId(Cart $cart)
 * @method static PaymentIntent fetchOrCreateIntent(Cart $cart, array $createOptions = [])
 * @method static PaymentMethod|null getPaymentMethod(string $paymentMethodId)
 * @method static PaymentIntent createIntent(Cart $cart, array $opts = [])
 * @method static void updateShippingAddress(Cart $cart)
 * @method static void updateIntent(Cart $cart, array $values)
 * @method static void updateIntentById(string $id, array $values)
 * @method static void syncIntent(Cart $cart)
 * @method static void cancelIntent(Cart $cart, CancellationReason $reason)
 * @method static PaymentIntent|null fetchIntent(string $intentId, void $options = null)
 * @method static Collection getCharges(string $paymentIntentId)
 * @method static Charge getCharge(string $chargeId)
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

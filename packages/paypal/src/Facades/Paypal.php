<?php

namespace Lunar\Paypal\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Paypal\PaypalInterface;

/**
 * @method static \Illuminate\Http\Client\PendingRequest baseHttpClient()
 * @method static string getApiUrl()
 * @method static string|null getAccessToken()
 * @method static array getOrder(string $orderId)
 * @method static array capture(string $orderId)
 * @method static array refund(void $transactionId, string $amount, string $currencyCode)
 * @method static array buildInitialOrder(\Lunar\Models\Contracts\Cart $cart)
 *
 * @see \Lunar\Paypal\Paypal
 */
class Paypal extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PaypalInterface::class;
    }
}

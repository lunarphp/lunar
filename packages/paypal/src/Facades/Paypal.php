<?php

namespace Lunar\Paypal\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Paypal\PaypalInterface;

/**
 * @method static void baseHttpClient()
 * @method static void getApiUrl()
 * @method static void getAccessToken()
 * @method static void getOrder(string $orderId)
 * @method static void capture(string $orderId)
 * @method static void refund(void $transactionId, string $amount, string $currencyCode)
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

<?php

namespace Lunar\Paypal\Facades;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Models\Cart;
use Lunar\Paypal\Contracts\PaypalInterface;

/**
 * @method static PendingRequest baseHttpClient()
 * @method static PendingRequest httpClient()
 * @method static string getApiUrl()
 * @method static string|null getAccessToken()
 * @method static array<string, mixed> getOrder(string $orderId)
 * @method static array<string, mixed> capture(string $orderId, ?string $requestId = null)
 * @method static array<string, mixed> authorizeOrder(string $orderId, ?string $requestId = null)
 * @method static array<string, mixed> captureAuthorization(string $authorizationId, ?string $amount = null, ?string $currencyCode = null, ?string $requestId = null)
 * @method static array<string, mixed> refund(string $transactionId, string $amount, string $currencyCode, ?string $requestId = null)
 * @method static array<string, mixed> buildInitialOrder(Cart $cart)
 * @method static bool verifyWebhookSignature(array<string, mixed> $headers, array<string, mixed> $payload)
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

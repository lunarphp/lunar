<?php

namespace Lunar\Paypal;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Lunar\Core\Models\Cart;
use Lunar\Paypal\Contracts\PaypalInterface;
use Lunar\Paypal\Managers\PaypalManager;

class Paypal implements PaypalInterface
{
    public function __construct(
        protected Factory $http,
        protected Repository $config,
        protected CacheRepository $cache,
    ) {}

    public function baseHttpClient(): PendingRequest
    {
        return $this->http->baseUrl($this->getApiUrl());
    }

    public function httpClient(): PendingRequest
    {
        return $this->baseHttpClient()->withToken((string) $this->getAccessToken());
    }

    public function getApiUrl(): string
    {
        return $this->setting('env', 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function getAccessToken(): ?string
    {
        $clientId = $this->setting('client_id');
        $secret = $this->setting('secret');

        if (! $clientId || ! $secret) {
            return null;
        }

        // Keyed on the environment so switching between sandbox and live can't
        // reuse a token minted against the other one.
        $cacheKey = 'lunar.paypal.access_token.'.md5($this->getApiUrl().$clientId);

        if ($token = $this->cache->get($cacheKey)) {
            return $token;
        }

        $response = $this->baseHttpClient()
            ->withBasicAuth($clientId, $secret)
            ->asForm()
            ->post('v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->json();

        if (! $token = ($response['access_token'] ?? null)) {
            return null;
        }

        // A minute short of PayPal's stated lifetime, so a token can't expire
        // mid-request.
        $this->cache->put($cacheKey, $token, max((int) ($response['expires_in'] ?? 0) - 60, 60));

        return $token;
    }

    public function getOrder(string $orderId): array
    {
        return $this->httpClient()->get("/v2/checkout/orders/{$orderId}")->json() ?? [];
    }

    public function capture(string $orderId, ?string $requestId = null): array
    {
        return $this->httpClient()
            ->withHeaders($this->idempotencyHeaders($requestId))
            ->withBody('', 'application/json')
            ->post("/v2/checkout/orders/{$orderId}/capture")
            ->json() ?? [];
    }

    public function captureAuthorization(string $authorizationId, ?string $amount = null, ?string $currencyCode = null, ?string $requestId = null): array
    {
        $payload = $amount === null ? [] : [
            'amount' => [
                'value' => $amount,
                'currency_code' => $currencyCode,
            ],
            'final_capture' => true,
        ];

        return $this->httpClient()
            ->withHeaders($this->idempotencyHeaders($requestId))
            ->withBody(json_encode($payload), 'application/json')
            ->post("/v2/payments/authorizations/{$authorizationId}/capture")
            ->throw()
            ->json() ?? [];
    }

    public function refund(string $transactionId, string $amount, string $currencyCode, ?string $requestId = null): array
    {
        return $this->httpClient()
            ->withHeaders($this->idempotencyHeaders($requestId))
            ->withBody(json_encode([
                'amount' => [
                    'value' => $amount,
                    'currency_code' => $currencyCode,
                ],
            ]), 'application/json')
            ->post("/v2/payments/captures/{$transactionId}/refund")
            ->throw()
            ->json() ?? [];
    }

    public function buildInitialOrder(Cart $cart): array
    {
        // The total is null until the cart is calculated, and reading through it
        // blind is how this used to fatal.
        $cart = $cart->total ? $cart : $cart->calculate();

        $billingAddress = $cart->billingAddress;
        $shippingAddress = $cart->shippingAddress ?: $billingAddress;

        $requestData = [
            'intent' => $this->setting('policy', 'automatic') === 'manual' ? 'AUTHORIZE' : 'CAPTURE',
            'purchase_units' => [
                [
                    'shipping' => [
                        'type' => $shippingAddress ? 'SHIPPING' : 'PICKUP_IN_PERSON',
                        'address' => $this->addressPayload($shippingAddress),
                    ],
                    'amount' => [
                        'currency_code' => $cart->currency->code,
                        'value' => PaypalManager::toPaypalAmount($cart->total->value, $cart->currency),
                    ],
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'user_action' => 'PAY_NOW',
                    'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                    'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                    'return_url' => route($this->setting('success_route', 'checkout.success')),
                    'cancel_url' => route($this->setting('cancel_route', 'checkout.cancel')),
                    'name' => [
                        'given_name' => $billingAddress?->first_name,
                        'surname' => $billingAddress?->last_name,
                    ],
                    'email_address' => $billingAddress?->contact_email,
                    'address' => $this->addressPayload($billingAddress),
                ],
            ],
        ];

        return $this->httpClient()->withBody(
            json_encode($requestData), 'application/json'
        )->post('v2/checkout/orders')->json() ?? [];
    }

    public function verifyWebhookSignature(array $headers, array $payload): bool
    {
        if (! $webhookId = $this->setting('webhook_id')) {
            return false;
        }

        $response = $this->httpClient()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $headers['paypal-auth-algo'] ?? null,
            'cert_url' => $headers['paypal-cert-url'] ?? null,
            'transmission_id' => $headers['paypal-transmission-id'] ?? null,
            'transmission_sig' => $headers['paypal-transmission-sig'] ?? null,
            'transmission_time' => $headers['paypal-transmission-time'] ?? null,
            'webhook_id' => $webhookId,
            'webhook_event' => $payload,
        ])->json();

        return ($response['verification_status'] ?? null) === 'SUCCESS';
    }

    /**
     * A PayPal address block, safe against a missing address.
     *
     * @return array<string, string|null>
     */
    protected function addressPayload(mixed $address): array
    {
        return [
            'address_line_1' => $address?->line_one,
            'address_line_2' => $address?->line_two,
            'admin_area_2' => $address?->city,
            'admin_area_1' => $address?->state,
            'postal_code' => $address?->postcode,
            'country_code' => $address?->country?->iso2,
        ];
    }

    /**
     * PayPal treats a repeated PayPal-Request-Id as the same call, so a retried
     * capture or refund cannot take the money twice.
     *
     * @return array<string, string>
     */
    protected function idempotencyHeaders(?string $requestId): array
    {
        return $requestId ? ['PayPal-Request-Id' => $requestId] : [];
    }

    /**
     * Read a driver setting, falling back to the deprecated `services.paypal.*`
     * location so existing installs keep working for one release.
     */
    protected function setting(string $key, mixed $default = null): mixed
    {
        return $this->config->get("lunar.paypal.{$key}")
            ?? $this->config->get("services.paypal.{$key}")
            ?? $default;
    }
}

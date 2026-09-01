<?php

namespace Lunar\Paypal\Contracts;

use Illuminate\Http\Client\PendingRequest;
use Lunar\Core\Models\Cart;

interface PaypalInterface
{
    /**
     * An HTTP client pointed at the configured PayPal API, without credentials.
     */
    public function baseHttpClient(): PendingRequest;

    /**
     * An HTTP client authenticated with a current access token.
     */
    public function httpClient(): PendingRequest;

    /**
     * The base URL of the API the driver is configured to talk to.
     */
    public function getApiUrl(): string;

    /**
     * A current OAuth access token, fetched and cached until shortly before it
     * expires. Null when credentials are missing or PayPal rejects them.
     */
    public function getAccessToken(): ?string;

    /**
     * Fetch a PayPal order.
     *
     * @return array<string, mixed>
     */
    public function getOrder(string $orderId): array;

    /**
     * Capture an approved PayPal order.
     *
     * @return array<string, mixed>
     */
    public function capture(string $orderId, ?string $requestId = null): array;

    /**
     * Authorize an approved PayPal order, holding the funds for later capture.
     *
     * @return array<string, mixed>
     */
    public function authorizeOrder(string $orderId, ?string $requestId = null): array;

    /**
     * Capture a previously authorized payment, optionally for part of the amount.
     *
     * @return array<string, mixed>
     */
    public function captureAuthorization(string $authorizationId, ?string $amount = null, ?string $currencyCode = null, ?string $requestId = null): array;

    /**
     * Refund a capture.
     *
     * @return array<string, mixed>
     */
    public function refund(string $transactionId, string $amount, string $currencyCode, ?string $requestId = null): array;

    /**
     * Create the PayPal order a customer approves to pay for a cart.
     *
     * @return array<string, mixed>
     */
    public function buildInitialOrder(Cart $cart): array;

    /**
     * Ask PayPal whether an inbound webhook signature is genuine.
     *
     * @param  array<string, mixed>  $headers
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhookSignature(array $headers, array $payload): bool;
}

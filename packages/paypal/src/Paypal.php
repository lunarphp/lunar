<?php

namespace Lunar\Paypal;

use Lunar\Models\Cart;
use Illuminate\Support\Facades\Http;

class Paypal implements PaypalInterface
{
    private $accessToken;

    public function baseHttpClient()
    {
        return Http::baseUrl(
            $this->getApiUrl()
        );
    }

    public function getApiUrl()
    {
        return config('services.paypal.env', 'sandbox') == 'sandbox' ?
            'https://api-m.sandbox.paypal.com':
            'https://api-m.live.paypal.com';
    }

    public function getAccessToken()
    {
        return $this->accessToken ?: $this->accessToken = $this->baseHttpClient()->withBasicAuth(
            config('services.paypal.client_id'),
            config('services.paypal.secret'),
        )->asForm()->post(
            'v1/oauth2/token',
            [
                'grant_type' => 'client_credentials'
            ]
        )->json()['access_token'] ?? null;
    }

    public function getOrder(string $orderId)
    {
        return $this->baseHttpClient()->withToken($this->getAccessToken())
            ->get("/v2/checkout/orders/{$orderId}")
            ->json();
    }

    public function capture(string $orderId)
    {
        return $this->baseHttpClient()->withToken($this->getAccessToken())
            ->withBody('', 'application/json')
            ->post("/v2/checkout/orders/{$orderId}/capture")
            ->json();
    }

    /**
     * @param Cart $cart
     * @return array
     */
    public function buildInitialOrder(Cart $cart): array
    {
        $billingAddress = $cart->billingAddress;
        $shippingAddress = $cart->shippingAddress ?: $billingAddress;

        $successRoute = config('lunar.payments.paypal.success_route', 'checkout.success');

        $requestData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'shipping' => [
                        'type' => $shippingAddress ? 'SHIPPING' : 'PICKUP_IN_PERSON',
                        'address' => [
                            'address_line_1' => $shippingAddress?->line_one,
                            'address_line_2' => $shippingAddress?->line_two,
                            'admin_area_2' => $shippingAddress?->city,
                            'admin_area_1' => $shippingAddress?->state,
                            'postal_code' => $shippingAddress?->postcode,
                            'country_code' => $shippingAddress?->country?->iso2,
                        ]
                    ],
                    'amount' => [
                        'currency_code' => $cart->currency->code,
                        'value' => (string) $cart->total->decimal,
                    ],
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'user_action' => 'PAY_NOW',
                    'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                    'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                    'email' => $billingAddress?->contact_email,
                    'return_url' => route($successRoute),
                    'cancel_url' => route($successRoute, $cart->fingerprint()),
                    'name' => [
                        'given_name' => $billingAddress?->first_name,
                        'surname' => $billingAddress?->last_name,
                    ],
                    'email_address' => $billingAddress->contact_email,
                    'address' => [
                        'address_line_1' => $billingAddress?->line_one,
                        'address_line_2' => $billingAddress?->line_two,
                        'admin_area_2' => $billingAddress?->city,
                        'admin_area_1' => $billingAddress?->state,
                        'postal_code' => $billingAddress?->postcode,
                        'country_code' => $billingAddress?->country?->iso2,
                    ],
                ],
            ],
        ];

        return $this->baseHttpClient()->withToken($this->getAccessToken())->withBody(
            json_encode($requestData), 'application/json'
        )->post('v2/checkout/orders')->json();
    }
}
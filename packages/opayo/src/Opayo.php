<?php

namespace Lunar\Opayo;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lunar\Opayo\DataTransferObjects\AuthPayloadParameters;

class Opayo implements OpayoInterface
{
    /**
     * The Http client
     *
     * @var Http
     */
    protected $http;

    public function __construct()
    {
        $this->http = Http::baseUrl(
            strtolower(config('services.opayo.env', 'test')) == 'test' ?
             'https://sandbox.opayo.eu.elavon.com/api/v1/' :
             'https://live.opayo.eu.elavon.com/api/v1/'
        )->withHeaders([
            'Authorization' => 'Basic '.$this->getCredentials(),
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Return the merchant key for payment.
     */
    public function getMerchantKey(): ?string
    {
        $response = $this->http->post('merchant-session-keys', [
            'vendorName' => $this->getVendor(),
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json()['merchantSessionKey'] ?? null;
    }

    /**
     * Return the Http client.
     */
    public function api(): PendingRequest
    {
        return $this->http;
    }

    /**
     * Return a transaction from the API
     */
    public function getTransaction(string $id, $attempt = 1): ?object
    {
        $response = $this->http->get("transactions/{$id}");

        if (! $response->successful()) {
            if ($attempt > 4) {
                return null;
            }

            sleep(1);

            return $this->getTransaction($id, $attempt + 1);
        }

        return $response->object();
    }

    public function getAuthPayload(AuthPayloadParameters $parameters): array
    {
        $payload = [
            'transactionType' => $parameters->transactionType,
            'paymentMethod' => [
                'card' => [
                    'merchantSessionKey' => $parameters->merchantSessionKey,
                    'cardIdentifier' => $parameters->cardIdentifier,
                ],
            ],
            'vendorTxCode' => $parameters->vendorTxCode,
            'amount' => $parameters->amount,
            'currency' => Str::take($parameters->currency, 3),
            'description' => 'Webstore Transaction',
            'apply3DSecure' => 'UseMSPSetting',
            'customerFirstName' => Str::take($parameters->customerFirstName, 20),
            'customerLastName' => Str::take($parameters->customerLastName, 20),
            'billingAddress' => [
                'address1' => Str::take($parameters->billingAddressLineOne, 50),
                'address2' => Str::take($parameters->billingAddressLineTwo, 50),
                'address3' => Str::take($parameters->billingAddressLineThree, 50),
                'city' => Str::take($parameters->billingAddressCity, 40),
                'postalCode' => Str::take($parameters->billingAddressPostcode, 10),
                'country' => Str::take($parameters->billingAddressCountryIso, 2),
            ],
            'strongCustomerAuthentication' => [
                'customerMobilePhone' => Str::take($parameters->customerMobilePhone, 19),
                'transType' => 'GoodsAndServicePurchase',
                'browserLanguage' => Str::take($parameters->browserLanguage, 8),
                'challengeWindowSize' => $parameters->challengeWindowSize,
                'browserIP' => Str::take($parameters->browserIP, 39),
                'notificationURL' => $parameters->notificationURL,
                'browserAcceptHeader' => Str::take($parameters->browserAcceptHeader, 2048),
                'browserJavascriptEnabled' => true,
                'browserUserAgent' => Str::take($parameters->browserUserAgent, 2048),
                'browserJavaEnabled' => $parameters->browserJavaEnabled,
                'browserColorDepth' => Str::take($parameters->browserColorDepth, 2),
                'browserScreenHeight' => Str::take($parameters->browserScreenHeight, 6),
                'browserScreenWidth' => Str::take($parameters->browserScreenWidth, 6),
                'browserTZ' => Str::take($parameters->browserTZ, 6),
            ],
            'entryMethod' => 'Ecommerce',
        ];

        if ($parameters->shippingAddressLineOne) {
            $payload['shippingDetails'] = [
                'recipientFirstName' => Str::take($parameters->recipientFirstName, 20),
                'recipientLastName' => Str::take($parameters->recipientLastName, 20),
                'shippingAddress1' => Str::take($parameters->shippingAddressLineOne, 50),
                'shippingAddress2' => Str::take($parameters->shippingAddressLineTwo, 50),
                'shippingAddress3' => Str::take($parameters->shippingAddressLineThree, 50),
                'shippingCity' => Str::take($parameters->shippingAddressCity, 40),
                'shippingPostalCode' => Str::take($parameters->shippingAddressPostcode, 10),
                'shippingCountry' => Str::take($parameters->shippingAddressCountryIso, 2),
            ];
        }

        if ($parameters->saveCard) {
            $payload['credentialType'] = [
                'cofUsage' => 'First',
                'initiatedType' => 'CIT',
                'mitType' => 'Unscheduled',
            ];
            $payload['paymentMethod']['card']['save'] = true;
        }

        if ($parameters->reusable) {
            $payload['credentialType'] = [
                'cofUsage' => 'Subsequent',
                'initiatedType' => 'CIT',
                'mitType' => 'Unscheduled',
            ];
            $payload['paymentMethod']['card']['reusable'] = true;
        }

        if ($parameters->authCode) {
            $payload['strongCustomerAuthentication']['threeDSRequestorPriorAuthenticationInfo']['threeDSReqPriorRef'] = $parameters->authCode;
        }

        return $payload;
    }

    /**
     * Get the service credentials.
     */
    protected function getCredentials(): string
    {
        return base64_encode(config('services.opayo.key').':'.config('services.opayo.password'));
    }

    /**
     * Get the vendor name.
     */
    protected function getVendor(): string
    {
        return config('services.opayo.vendor');
    }
}

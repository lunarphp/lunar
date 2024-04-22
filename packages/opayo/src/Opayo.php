<?php

namespace Lunar\Opayo;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
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
            'currency' => $parameters->currency,
            'description' => 'Webstore Transaction',
            'apply3DSecure' => 'UseMSPSetting',
            'customerFirstName' => $parameters->customerFirstName,
            'customerLastName' => $parameters->customerLastName,
            'billingAddress' => [
                'address1' => $parameters->billingAddressLineOne,
                'city' => $parameters->billingAddressCity,
                'postalCode' => $parameters->billingAddressPostcode,
                'country' => $parameters->billingAddressCountryIso,
            ],
            'strongCustomerAuthentication' => [
                'customerMobilePhone' => $parameters->customerMobilePhone,
                'transType' => 'GoodsAndServicePurchase',
                'browserLanguage' => $parameters->browserLanguage,
                'challengeWindowSize' => $parameters->challengeWindowSize,
                'browserIP' => $parameters->browserIP,
                'notificationURL' => $parameters->notificationURL,
                'browserAcceptHeader' => $parameters->browserAcceptHeader,
                'browserJavascriptEnabled' => true,
                'browserUserAgent' => $parameters->browserUserAgent,
                'browserJavaEnabled' => $parameters->browserJavaEnabled,
                'browserColorDepth' => $parameters->browserColorDepth,
                'browserScreenHeight' => $parameters->browserScreenHeight,
                'browserScreenWidth' => $parameters->browserScreenWidth,
                'browserTZ' => $parameters->browserTZ,
            ],
            'entryMethod' => 'Ecommerce',
        ];

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

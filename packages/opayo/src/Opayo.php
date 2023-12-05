<?php

namespace Lunar\Opayo;

use Illuminate\Support\Facades\Http;

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
             'https://pi-test.sagepay.com/api/v1/' :
             'https://pi-live.sagepay.com/api/v1/'
        )->withHeaders([
            'Authorization' => 'Basic '.$this->getCredentials(),
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /**
     * Return the merchant key for payment.
     *
     * @return string
     */
    public function getMerchantKey()
    {
        $response = $this->http->post('merchant-session-keys', [
            'vendorName' => $this->getVendor(),
        ]);

        if (! $response->successful()) {
            return;
        }

        return $response->json()['merchantSessionKey'] ?? null;
    }

    /**
     * Return the Http client.
     */
    public function api()
    {
        return $this->http;
    }

    /**
     * Return a transaction from the API
     *
     * @param  string  $id
     * @return mixed
     */
    public function getTransaction($id, $attempt = 1)
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

    /**
     * Get the service credentials.
     *
     * @return string
     */
    protected function getCredentials()
    {
        return base64_encode(config('services.opayo.key').':'.config('services.opayo.password'));
    }

    /**
     * Get the vendor name.
     *
     * @return string
     */
    protected function getVendor()
    {
        return config('services.opayo.vendor');
    }
}

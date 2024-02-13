<?php

namespace Lunar\Tests\Stripe\Stripe;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Stripe\HttpClient\ClientInterface;

class MockClient implements ClientInterface
{
    public $rbody = '{}';

    public $rcode = 200;

    public $rheaders = [];

    public $url;

    public function __construct()
    {
        $this->url = 'https://checkout.stripe.com/pay/cs_test_'.Str::random(32);
    }

    public function request($method, $absUrl, $headers, $params, $hasFile)
    {
        $id = array_slice(explode('/', $absUrl), -1)[0];

        $policy = config('lunar.stripe.policy');

        if ($method == 'get' && str_contains($absUrl, 'payment_intents')) {
            if (str_contains($absUrl, 'PI_CAPTURE')) {
                $this->rBody = $this->getResponse('payment_intent_paid', [
                    'id' => $id,
                    'status' => 'succeeded',
                    'capture_method' => 'automatic',
                    'payment_status' => 'succeeded',
                    'payment_error' => null,
                    'failure_code' => null,
                    'captured' => true,
                ]);

                return [$this->rBody, $this->rcode, $this->rheaders];
            }

            if (str_contains($absUrl, 'PI_FAIL')) {
                $this->rBody = $this->getResponse('payment_intent_paid', [
                    'id' => $id,
                    'status' => 'requires_payment_method',
                    'capture_method' => 'automatic',
                    'payment_status' => 'failed',
                    'payment_error' => 'foo',
                    'failure_code' => 1234,
                    'captured' => false,
                ]);

                return [$this->rBody, $this->rcode, $this->rheaders];
            }

            if (str_contains($absUrl, 'PI_REQUIRES_PAYMENT_METHOD')) {
                $this->rBody = $this->getResponse('payment_intent_requires_payment_method');

                return [$this->rBody, $this->rcode, $this->rheaders];
            }

        }

        if ($method == 'post' && str_contains($absUrl, 'payment_intents')) {
            $this->rBody = $this->getResponse('payment_intent_created');

            return [$this->rBody, $this->rcode, $this->rheaders];
        }

        if ($method == 'get' && str_contains($absUrl, 'payment_intents')) {
            $this->rBody = $this->getResponse('payment_intent_created', [
                'id' => $id,
            ]);

            return [$this->rBody, $this->rcode, $this->rheaders];
        }

        dd($method, $absUrl, $headers, $params, $hasFile);

        // // Handle Laravel Cashier creating/getting a customer
        // if ($method == "get" && strpos($absUrl, "https://api.stripe.com/v1/customers/") === 0) {
        //     $this->rBody = $this->getCustomer(str_replace("https://api.stripe.com/v1/customers/", "", $absUrl));
        //     return [$this->rBody, $this->rcode, $this->rheaders];
        // }

        // if ($method == "post" && $absUrl == "https://api.stripe.com/v1/customers") {
        //     $this->rBody = $this->getCustomer("cus_".Str::random(14));
        //     return [$this->rBody, $this->rcode, $this->rheaders];
        // }

        // // Handle creating a Stripe Checkout session
        // if ($method == "post" && $absUrl == "https://api.stripe.com/v1/checkout/sessions") {
        //     $this->rBody = $this->getSession($this->url);
        //     return [$this->rBody, $this->rcode, $this->rheaders];
        // }

        // return [$this->rbody, $this->rcode, $this->rheaders];
    }

    /**
     * Fetches a response for the mock
     *
     * @param  string  $filename
     * @param  array  $replace
     * @return string
     */
    protected function getResponse($filename, $replace = [])
    {
        $response = File::get(__DIR__.'/responses/'.$filename.'.json');

        foreach ($replace as $token => $value) {
            $response = str_replace('{'.$token.'}', $value, $response);
        }

        return $response;
    }
}

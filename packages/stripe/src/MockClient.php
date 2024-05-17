<?php

namespace Lunar\Stripe;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Stripe\HttpClient\ClientInterface;
use Stripe\PaymentIntent;

class MockClient implements ClientInterface
{
    public string $rBody = '{}';

    public int $rcode = 200;

    public array $rheaders = [];

    public string $url;

    public function __construct()
    {
        $this->url = 'https://checkout.stripe.com/pay/cs_test_'.Str::random(32);
    }

    public function request($method, $absUrl, $headers, $params, $hasFile)
    {
        $id = array_slice(explode('/', $absUrl), -1)[0];

        $policy = config('lunar.stripe.policy');

        if ($method == 'get' && str_contains($absUrl, 'charges')) {

            $status = 'succeeded';
            $failureCode = null;

            if (($params['payment_intent'] ?? null) == 'PI_FAIL') {
                $status = 'failed';
                $failureCode = 'FAILED';
            }

            $this->rBody = $this->getResponse('charges', [
                'status' => $status,
                'failure_code' => $failureCode,
            ]);

            return [$this->rBody, $this->rcode, $this->rheaders];
        }

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

            if (str_contains($absUrl, 'PI_REQUIRES_ACTION')) {
                $this->rBody = $this->getResponse('payment_intent_paid', [
                    'id' => $id,
                    'status' => PaymentIntent::STATUS_REQUIRES_ACTION,
                    'capture_method' => 'automatic',
                    'payment_status' => 'failed',
                    'payment_error' => 'foo',
                    'failure_code' => 1234,
                    'captured' => false,
                ]);

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

        return [$this->rBody, $this->rcode, $this->rheaders];
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
        $response = File::get(__DIR__.'/../resources/responses/'.$filename.'.json');

        foreach ($replace as $token => $value) {
            $response = str_replace('{'.$token.'}', $value, $response);
        }

        return $response;
    }
}

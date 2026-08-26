<?php

namespace Lunar\Stripe;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Stripe\HttpClient\ClientInterface;
use Stripe\PaymentIntent;

class MockClient implements ClientInterface
{
    public string $rBody = '{}';

    public array $nextData = [];

    public int $rcode = 200;

    public array $rheaders = [];

    public string $url;

    /**
     * Every request made against the mock, for asserting outgoing payloads.
     *
     * @var array<int, array{method: string, url: string, params: ?array}>
     */
    public array $requests = [];

    private bool $failThenCaptureCalled = false;

    public function __construct()
    {
        $this->url = 'https://checkout.stripe.com/pay/cs_test_'.Str::random(32);
    }

    public function next(array $data): self
    {
        $this->nextData = $data;

        return $this;
    }

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1')
    {
        $this->requests[] = ['method' => $method, 'url' => $absUrl, 'params' => $params];

        $id = array_slice(explode('/', $absUrl), -1)[0];

        $policy = config('lunar.stripe.policy');

        if ($method == 'get' && str_contains($absUrl, 'charges/CH_LINK')) {
            $this->rBody = $this->getResponse('charge_link', [
                'status' => 'succeeded',
                ...$this->nextData,
            ]);

            return [$this->rBody, $this->rcode, $this->rheaders];
        }

        if ($method == 'get' && str_contains($absUrl, 'charges/')) {
            $this->rBody = $this->getResponse('charge', [
                'id' => $id,
                'payment_intent' => 'PI_CAPTURE',
                ...$this->nextData,
            ]);

            return [$this->rBody, $this->rcode, $this->rheaders];
        }

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
                ...$this->nextData,
            ]);

            return [$this->rBody, $this->rcode, $this->rheaders];
        }

        if ($method == 'get' && str_contains($absUrl, 'payment_intents')) {
            if (str_contains($absUrl, 'PI_CAPTURE_LINK')) {
                $this->rBody = $this->getResponse('payment_intent_paid', [
                    'id' => $id,
                    'status' => 'succeeded',
                    'capture_method' => 'automatic',
                    'latest_charge_id' => 'CH_LINK',
                    'payment_status' => 'succeeded',
                    'payment_method_id' => 'PM_LINK',
                    'payment_error' => null,
                    'failure_code' => null,
                    'captured' => true,
                    ...$this->nextData,
                ]);

                return [$this->rBody, $this->rcode, $this->rheaders];
            }

            if (str_contains($absUrl, 'PI_CAPTURE')) {
                $this->rBody = $this->getResponse('payment_intent_paid', [
                    'id' => $id,
                    'status' => 'succeeded',
                    'capture_method' => 'automatic',
                    'latest_charge_id' => 'CH_CARD',
                    'payment_status' => 'succeeded',
                    'payment_method_id' => 'PM_CARD',
                    'payment_error' => null,
                    'failure_code' => null,
                    'captured' => true,
                    'amount' => 2000,
                    ...$this->nextData,
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
                    ...$this->nextData,
                ]);

                return [$this->rBody, $this->rcode, $this->rheaders];
            }

            if (str_contains($absUrl, 'PI_REQUIRES_PAYMENT_METHOD')) {
                $this->rBody = $this->getResponse('payment_intent_requires_payment_method', [
                    ...$this->nextData,
                ]);

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
                    ...$this->nextData,
                ]);

                return [$this->rBody, $this->rcode, $this->rheaders];
            }

            if (str_contains($absUrl, 'PI_FIRST_FAIL_THEN_CAPTURE')) {
                $succeeded = $this->failThenCaptureCalled;
                $this->rBody = $this->getResponse('payment_intent_paid', [
                    'id' => $id,
                    'status' => $succeeded ? 'succeeded' : 'requires_payment_method',
                    'capture_method' => 'automatic',
                    'payment_status' => $succeeded ? 'succeeded' : 'failed',
                    'payment_error' => $succeeded ? null : 'failed',
                    'failure_code' => $succeeded ? null : 1234,
                    'captured' => $succeeded,
                    ...$this->nextData,
                ]);

                $this->failThenCaptureCalled = true;

                return [$this->rBody, $this->rcode, $this->rheaders];
            }
        }

        if ($method == 'post' && str_contains($absUrl, 'refunds')) {
            $this->rBody = $this->getResponse('refund', [
                'refund_amount' => $params['amount'] ?? 0,
                'payment_intent' => $params['payment_intent'] ?? 'PI_CAPTURE',
            ]);

            return [$this->rBody, $this->rcode, $this->rheaders];
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

        if ($method == 'get' && str_contains($absUrl, 'payment_methods')) {
            $paymentMethod = str_contains($absUrl, 'PM_LINK') ? 'payment_method_link' : 'payment_method';

            $this->rBody = $this->getResponse($paymentMethod, [
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

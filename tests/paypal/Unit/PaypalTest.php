<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Paypal\Facades\Paypal;
use Lunar\Tests\Paypal\Unit\TestCase;
use Lunar\Tests\Paypal\Utils\CartBuilder;
use Lunar\Tests\Paypal\Utils\PaypalFake;

uses(TestCase::class);

it('targets the sandbox api by default', function () {
    expect(Paypal::getApiUrl())->toEqual('https://api-m.sandbox.paypal.com');
});

it('targets the live api when configured', function () {
    Config::set('services.paypal.env', 'live');

    expect(Paypal::getApiUrl())->toEqual('https://api-m.paypal.com');
});

it('exchanges credentials for an access token', function () {
    PaypalFake::fake();

    expect(Paypal::getAccessToken())->toEqual('A21AAL_TEST_ACCESS_TOKEN');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'v1/oauth2/token')
        && $request['grant_type'] === 'client_credentials');
});

it('fetches an order', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders/*' => 'order_approved',
    ]);

    expect(Paypal::getOrder('5O190127TN364715T')['status'])->toEqual('APPROVED');
});

it('captures an order', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders/*/capture' => 'order_captured',
    ]);

    expect(Paypal::capture('5O190127TN364715T')['status'])->toEqual('COMPLETED');
});

it('refunds a capture', function () {
    PaypalFake::fake([
        '*/v2/payments/captures/*/refund' => 'refund',
    ]);

    $response = Paypal::refund('3C679366HH908993F', '19.99', 'GBP');

    expect($response['status'])->toEqual('COMPLETED');

    expect(PaypalFake::sentBody('*/v2/payments/captures/*/refund'))
        ->toEqual([
            'amount' => [
                'value' => '19.99',
                'currency_code' => 'GBP',
            ],
        ]);
});

it('builds an order payload from a calculated cart', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders' => 'order_created',
    ]);

    $cart = CartBuilder::build()->calculate();

    Paypal::buildInitialOrder($cart);

    $payload = PaypalFake::sentBody('*/v2/checkout/orders');

    expect($payload['intent'])->toEqual('CAPTURE')
        ->and($payload['purchase_units'][0]['amount']['currency_code'])->toEqual($cart->currency->code)
        ->and($payload['purchase_units'][0]['amount']['value'])->toEqual((string) $cart->total->decimal());
});

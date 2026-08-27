<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Paypal\Facades\Paypal;
use Lunar\Paypal\Managers\PaypalManager;
use Lunar\Tests\Paypal\Unit\TestCase;
use Lunar\Tests\Paypal\Utils\CartBuilder;
use Lunar\Tests\Paypal\Utils\PaypalFake;

uses(TestCase::class);

it('targets the sandbox api by default', function () {
    expect(Paypal::getApiUrl())->toEqual('https://api-m.sandbox.paypal.com');
});

it('targets the live api when configured', function () {
    Config::set('lunar.paypal.env', 'live');

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

it('falls back to the deprecated services.paypal config', function () {
    Config::set('lunar.paypal.env', null);
    Config::set('services.paypal.env', 'live');

    expect(Paypal::getApiUrl())->toEqual('https://api-m.paypal.com');
});

it('caches the access token until shortly before it expires', function () {
    PaypalFake::fake();

    expect(Paypal::getAccessToken())->toEqual('A21AAL_TEST_ACCESS_TOKEN')
        ->and(Paypal::getAccessToken())->toEqual('A21AAL_TEST_ACCESS_TOKEN');

    // The fixture's expires_in is 32400; only one exchange should have happened.
    Http::assertSentCount(1);
});

it('returns no access token when credentials are missing', function () {
    Config::set('lunar.paypal.client_id', null);
    Config::set('lunar.paypal.secret', null);

    PaypalFake::fake();

    expect(Paypal::getAccessToken())->toBeNull();

    Http::assertNothingSent();
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
        ->and($payload['purchase_units'][0]['amount']['value'])
        ->toEqual(PaypalManager::toPaypalAmount($cart->total->value, $cart->currency));
});

it('calculates an uncalculated cart rather than fataling on a null total', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders' => 'order_created',
    ]);

    // Never calculated, so `$cart->total` is null.
    $cart = CartBuilder::build();

    expect($cart->total)->toBeNull();

    Paypal::buildInitialOrder($cart);

    expect(PaypalFake::sentBody('*/v2/checkout/orders')['purchase_units'][0]['amount']['value'])
        ->not->toBeNull();
});

it('points the cancel url at the cancel route, not the success route', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders' => 'order_created',
    ]);

    Paypal::buildInitialOrder(CartBuilder::build()->calculate());

    $source = PaypalFake::sentBody('*/v2/checkout/orders')['payment_source']['paypal'];

    expect($source['return_url'])->toEqual(route('checkout.success'))
        ->and($source['cancel_url'])->toEqual(route('checkout.cancel'))
        ->and($source['cancel_url'])->not->toEqual($source['return_url']);
});

it('survives a cart with no billing address', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders' => 'order_created',
    ]);

    $cart = CartBuilder::build()->calculate();
    $cart->addresses()->delete();

    Paypal::buildInitialOrder($cart->refresh()->calculate());

    expect(PaypalFake::sentBody('*/v2/checkout/orders'))->not->toBeNull();
});

it('requests an authorize intent under the manual policy', function () {
    Config::set('lunar.paypal.policy', 'manual');

    PaypalFake::fake([
        '*/v2/checkout/orders' => 'order_created',
    ]);

    Paypal::buildInitialOrder(CartBuilder::build()->calculate());

    expect(PaypalFake::sentBody('*/v2/checkout/orders')['intent'])->toEqual('AUTHORIZE');
});

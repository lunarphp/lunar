<?php

use Lunar\Core\Facades\CartSession;
use Lunar\Tests\Paypal\Unit\TestCase;
use Lunar\Tests\Paypal\Utils\CartBuilder;
use Lunar\Tests\Paypal\Utils\PaypalFake;

uses(TestCase::class);

it('returns only the fields the client needs', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders' => 'order_created',
    ]);

    CartSession::use(CartBuilder::build()->calculate());

    $response = test()->postJson(route('post.paypal.order'))->assertOk();

    expect($response->json())->toEqual([
        'id' => '5O190127TN364715T',
        'status' => 'CREATED',
        'approve_url' => 'https://www.sandbox.paypal.com/checkoutnow?token=5O190127TN364715T',
    ]);
});

it('refuses when there is no cart to pay for', function () {
    PaypalFake::fake();

    test()->postJson(route('post.paypal.order'))->assertStatus(422);
});

it('reports upstream failure when paypal returns no order', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders' => ['order_not_found', 422],
    ]);

    CartSession::use(CartBuilder::build()->calculate());

    test()->postJson(route('post.paypal.order'))->assertStatus(502);
});

it('rate limits the endpoint', function () {
    PaypalFake::fake([
        '*/v2/checkout/orders' => 'order_created',
    ]);

    CartSession::use(CartBuilder::build()->calculate());

    // Config default is 10 attempts a minute.
    foreach (range(1, 10) as $attempt) {
        test()->postJson(route('post.paypal.order'))->assertOk();
    }

    test()->postJson(route('post.paypal.order'))->assertStatus(429);
});

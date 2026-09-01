<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Lunar\Paypal\Jobs\ProcessPaypalWebhook;
use Lunar\Paypal\Models\PaypalOrder;
use Lunar\Tests\Paypal\Unit\TestCase;
use Lunar\Tests\Paypal\Utils\CartBuilder;
use Lunar\Tests\Paypal\Utils\PaypalFake;

uses(TestCase::class);

beforeEach(function () {
    Config::set('lunar.paypal.webhook_id', 'WH-TEST-ID');
});

function fakeSignature(bool $verified = true): void
{
    Http::fake([
        '*/v1/oauth2/token' => Http::response(PaypalFake::fixture('oauth_token')),
        '*/v1/notifications/verify-webhook-signature' => Http::response(
            PaypalFake::fixture($verified ? 'webhook_signature_verified' : 'webhook_signature_failed')
        ),
    ]);
}

function postWebhook(string $fixture)
{
    return test()->postJson(
        route('lunar.paypal.webhook'),
        PaypalFake::fixture($fixture),
        ['PAYPAL-TRANSMISSION-ID' => 'abc', 'PAYPAL-AUTH-ALGO' => 'SHA256withRSA']
    );
}

it('rejects a webhook whose signature does not verify', function () {
    Queue::fake();
    fakeSignature(verified: false);

    postWebhook('webhook_capture_completed')->assertStatus(400);

    Queue::assertNotPushed(ProcessPaypalWebhook::class);
});

it('rejects a webhook when no webhook id is configured', function () {
    Queue::fake();
    Config::set('lunar.paypal.webhook_id', null);
    fakeSignature();

    postWebhook('webhook_capture_completed')->assertStatus(400);

    Queue::assertNotPushed(ProcessPaypalWebhook::class);
});

it('accepts but does not queue an event type it does not handle', function () {
    Queue::fake();
    fakeSignature();

    $payload = PaypalFake::fixture('webhook_capture_completed');
    $payload['event_type'] = 'PAYMENT.CAPTURE.REVERSED';

    test()->postJson(route('lunar.paypal.webhook'), $payload)->assertOk();

    Queue::assertNotPushed(ProcessPaypalWebhook::class);
});

it('queues a handled event', function () {
    Queue::fake();
    fakeSignature();

    postWebhook('webhook_capture_completed')->assertOk();

    Queue::assertPushed(ProcessPaypalWebhook::class, fn (ProcessPaypalWebhook $job) => $job->payload['event_type'] === 'PAYMENT.CAPTURE.COMPLETED');
});

it('does not queue while the driver is already processing the order', function () {
    Queue::fake();
    fakeSignature();

    $cart = CartBuilder::build()->calculate();

    PaypalOrder::create([
        'paypal_order_id' => '5O190127TN364715T',
        'cart_id' => $cart->id,
        'processing_at' => now(),
    ]);

    postWebhook('webhook_capture_completed')->assertOk();

    Queue::assertNotPushed(ProcessPaypalWebhook::class);
});

it('records the event id against the paypal order', function () {
    Queue::fake();
    fakeSignature();

    $cart = CartBuilder::build()->calculate();

    $paypalOrder = PaypalOrder::create([
        'paypal_order_id' => '5O190127TN364715T',
        'cart_id' => $cart->id,
    ]);

    postWebhook('webhook_capture_completed')->assertOk();

    expect($paypalOrder->refresh()->event_id)->toEqual('WH-2WR32451HC0233532-67976317FL4543714');
});

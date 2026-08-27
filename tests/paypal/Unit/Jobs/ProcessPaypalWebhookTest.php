<?php

use Illuminate\Support\Facades\Event;
use Lunar\Core\Models\Transaction;
use Lunar\Paypal\Events\PaypalWebhookReceived;
use Lunar\Paypal\Jobs\ProcessPaypalWebhook;
use Lunar\Paypal\Models\PaypalOrder;
use Lunar\Tests\Paypal\Unit\TestCase;
use Lunar\Tests\Paypal\Utils\CartBuilder;
use Lunar\Tests\Paypal\Utils\PaypalFake;

uses(TestCase::class);

it('resolves the paypal order id from a checkout event', function () {
    expect(ProcessPaypalWebhook::resolvePaypalOrderId(PaypalFake::fixture('webhook_order_approved')))
        ->toEqual('5O190127TN364715T');
});

it('resolves the paypal order id from a capture event', function () {
    // Capture events are keyed by capture ID, with the order ID in supplementary data.
    $payload = PaypalFake::fixture('webhook_capture_completed');

    expect($payload['resource']['id'])->toEqual('3C679366HH908993F')
        ->and(ProcessPaypalWebhook::resolvePaypalOrderId($payload))->toEqual('5O190127TN364715T');
});

it('dispatches an event for every webhook it handles', function () {
    Event::fake([PaypalWebhookReceived::class]);

    (new ProcessPaypalWebhook(PaypalFake::fixture('webhook_capture_completed')))->handle();

    Event::assertDispatched(
        PaypalWebhookReceived::class,
        fn (PaypalWebhookReceived $event) => $event->eventType === 'PAYMENT.CAPTURE.COMPLETED'
    );
});

it('places an order the customer never came back to confirm', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart);

    PaypalOrder::create([
        'paypal_order_id' => '5O190127TN364715T',
        'cart_id' => $cart->id,
    ]);

    (new ProcessPaypalWebhook(PaypalFake::fixture('webhook_order_approved')))->handle();

    expect($cart->refresh()->completedOrder)->not->toBeNull()
        ->and($cart->completedOrder->placed_at)->not->toBeNull();
});

it('does not place an order twice', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart);

    PaypalOrder::create([
        'paypal_order_id' => '5O190127TN364715T',
        'cart_id' => $cart->id,
        'processed_at' => now(),
    ]);

    (new ProcessPaypalWebhook(PaypalFake::fixture('webhook_order_approved')))->handle();

    expect($cart->refresh()->completedOrder)->toBeNull();
});

it('records a refund raised in the paypal dashboard', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart);

    $order = $cart->createOrder();

    $order->transactions()->create([
        'success' => true,
        'type' => 'capture',
        'driver' => 'paypal',
        'amount' => 1999,
        'reference' => '3C679366HH908993F',
        'status' => 'COMPLETED',
        'card_type' => 'paypal',
        'captured_at' => now(),
    ]);

    PaypalOrder::create([
        'paypal_order_id' => '5O190127TN364715T',
        'cart_id' => $cart->id,
        'order_id' => $order->id,
        'processed_at' => now(),
    ]);

    (new ProcessPaypalWebhook(PaypalFake::fixture('webhook_capture_refunded')))->handle();

    $refund = $order->refresh()->transactions()->where('type', 'refund')->first();

    expect($refund)->not->toBeNull()
        ->and($refund->reference)->toEqual('1JU08902781691411')
        ->and($refund->amount)->toEqual(500)
        ->and($refund->notes)->toEqual('Refunded via PayPal');
});

it('does not duplicate a refund the driver already recorded', function () {
    $cart = CartBuilder::build()->calculate();

    PaypalFake::forCart($cart);

    $order = $cart->createOrder();

    // Written by the driver's own refund() when raised from the admin.
    $order->transactions()->create([
        'success' => true,
        'type' => 'refund',
        'driver' => 'paypal',
        'amount' => 500,
        'reference' => '1JU08902781691411',
        'status' => 'COMPLETED',
        'card_type' => 'paypal',
    ]);

    PaypalOrder::create([
        'paypal_order_id' => '5O190127TN364715T',
        'cart_id' => $cart->id,
        'order_id' => $order->id,
        'processed_at' => now(),
    ]);

    (new ProcessPaypalWebhook(PaypalFake::fixture('webhook_capture_refunded')))->handle();

    expect(Transaction::where('type', 'refund')->count())->toBe(1);
});

it('ignores an event for a paypal order it has never seen', function () {
    (new ProcessPaypalWebhook(PaypalFake::fixture('webhook_capture_refunded')))->handle();

    expect(Transaction::count())->toBe(0);
});

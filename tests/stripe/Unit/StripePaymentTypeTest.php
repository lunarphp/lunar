<?php

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Transaction;
use Lunar\Stripe\Facades\StripeFacade;
use Lunar\Stripe\StripePaymentType;
use Lunar\Tests\Stripe\Utils\CartBuilder;

uses(\Lunar\Tests\Stripe\Unit\TestCase::class);

it('can capture an order', function () {
    $cart = CartBuilder::build();
    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class);
    expect($response->success)->toBeTrue();
    expect($cart->refresh()->completedOrder->placed_at)->not()->toBeNull();
    expect($cart->meta['payment_intent'])->toEqual('PI_CAPTURE');

    $this->assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $cart->refresh()->completedOrder->id,
        'type' => 'capture',
    ]);
});

it('can handle failed payments', function () {
    $cart = CartBuilder::build();

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_FAIL',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class);
    expect($response->success)->toBeFalse();
    expect($cart->refresh()->completedOrder)->toBeNull();
    expect($cart->refresh()->draftOrder)->not()->toBeNull();

    $this->assertDatabaseMissing((new Transaction)->getTable(), [
        'order_id' => $cart->refresh()->draftOrder->id,
        'type' => 'capture',
    ]);
});

it('can retrieve existing payment intent', function () {
    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => 'PI_FOOBAR',
        ],
    ]);

    StripeFacade::createIntent($cart->calculate());

    expect($cart->refresh()->meta['payment_intent'])->toBe('PI_FOOBAR');
});

it('will fail if cart already has an order', function () {
    $cart = CartBuilder::build();
    $order = $cart->createOrder();
    $order->update([
        'placed_at' => now(),
    ]);

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_CAPTURE',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class);
    expect($response->success)->toBeFalse();
    expect($response->message)->toBe('Carts can only have one order associated to them.');
});

it('will fail if payment intent status is requires_payment_method', function () {
    $cart = CartBuilder::build();

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_REQUIRES_PAYMENT_METHOD',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class);
    expect($response->success)->toBeFalse();

    expect($cart->refresh()->completedOrder)->toBeNull();
});

it('create a pending transaction when status is requires_action', function () {
    $cart = CartBuilder::build();

    $payment = new StripePaymentType;

    $response = $payment->cart($cart)->withData([
        'payment_intent' => 'PI_REQUIRES_ACTION',
    ])->authorize();

    expect($response)->toBeInstanceOf(PaymentAuthorize::class);
    expect($response->success)->toBeFalse();

    expect($cart->refresh()->completedOrder)->toBeNull();
});

<?php

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Transaction;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\StripePaymentType;
use Lunar\Tests\Stripe\Utils\CartBuilder;
use function Pest\Laravel\assertDatabaseHas;

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

    assertDatabaseHas((new Transaction)->getTable(), [
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

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
        ->and($response->success)->toBeFalse()
        ->and($cart->refresh()->completedOrder)->toBeNull()
        ->and($cart->currentDraftOrder())->not()->toBeNull();

    assertDatabaseHas((new Transaction)->getTable(), [
        'order_id' => $cart->currentDraftOrder()->id,
        'type' => 'capture',
        'success' => false,
    ]);
})->group('noo');

it('can retrieve existing payment intent', function () {
    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => 'PI_FOOBAR',
        ],
    ]);

    Stripe::createIntent($cart->calculate());

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

    expect($response)->toBeInstanceOf(PaymentAuthorize::class)
    ->and($response->success)->toBeFalse()
    ->and($response->message)->toBeIn([
        'Carts can only have one order associated to them.',
        __('lunar::exceptions.carts.order_exists')
    ]);
})->group('foob');

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

it('can return correct payment checks', function () {
    \Lunar\Models\Currency::factory()->create();

    $cart = buildCart();

    $order = $cart->createOrder();

    $transactionA = \Lunar\Models\Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'pass',
            'address_postal_code_check' => 'pass',
            'cvc_check' => 'pass',
        ],
    ]);

    $transactionB = \Lunar\Models\Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'fail',
            'address_postal_code_check' => 'fail',
            'cvc_check' => 'fail',
        ],
    ]);

    $transactionC = \Lunar\Models\Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'unavailable',
            'address_postal_code_check' => 'unavailable',
            'cvc_check' => 'unavailable',
        ],
    ]);

    $transactionD = \Lunar\Models\Transaction::factory()->create([
        'order_id' => $order->id,
        'driver' => 'stripe',
        'meta' => [
            'address_line1_check' => 'unchecked',
            'address_postal_code_check' => 'unchecked',
            'cvc_check' => 'unchecked',
        ],
    ]);

    $paymentAChecks = $transactionA->paymentChecks();

    expect($paymentAChecks)->toHaveCount(3)
        ->and($paymentAChecks[0]->successful)
        ->toBe(true)
        ->and($paymentAChecks[1]->successful)
        ->toBe(true)
        ->and($paymentAChecks[2]->successful)
        ->toBe(true);

    $paymentBChecks = $transactionB->paymentChecks();

    expect($paymentBChecks)->toHaveCount(3)
        ->and($paymentBChecks[0]->successful)
        ->not
        ->toBe(true)
        ->and($paymentBChecks[1]->successful)
        ->not
        ->toBe(true)
        ->and($paymentBChecks[2]->successful)
        ->not
        ->toBe(true);

    $paymentCChecks = $transactionC->paymentChecks();

    expect($paymentCChecks)->toHaveCount(3)
        ->and($paymentCChecks[0]->successful)
        ->not
        ->toBe(true)
        ->and($paymentCChecks[1]->successful)
        ->not
        ->toBe(true)
        ->and($paymentCChecks[2]->successful)
        ->not
        ->toBe(true);

    $paymentDChecks = $transactionD->paymentChecks();

    expect($paymentDChecks)->toHaveCount(3)
        ->and($paymentCChecks[0]->successful)
        ->not
        ->toBe(true)
        ->and($paymentDChecks[1]->successful)
        ->not
        ->toBe(true)
        ->and($paymentDChecks[2]->successful)
        ->not
        ->toBe(true);

});

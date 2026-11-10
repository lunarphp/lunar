<?php

use Lunar\Stripe\Actions\StoreCharges;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;

uses(TestCase::class);

it('can store successful charge', function () {
    // Pin a two-decimal currency: the factory's random faker code can land on
    // a currency Stripe scales differently (JPY, HUF, ...), which makes the
    // stored amount diverge from the raw charge fixture amount.
    $cart = CartBuilder::build(currencyParams: [
        'code' => 'USD',
        'decimal_places' => 2,
    ]);

    $order = $cart->createOrder();

    $paymentIntent = Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_CAPTURE');

    $charges = collect($paymentIntent->charges->data);

    $order = app(StoreCharges::class)->store($order, $charges);

    expect($order->transactions)->toHaveCount(1);

    $charge = $charges->first();
    $transaction = $order->transactions->first();

    expect($transaction->type)->toBe('capture');
    expect($transaction->amount->value)->toBe($charge->amount);
    expect($transaction->reference)->toBe($charge->id);
})->group('lunar.stripe.actions');

it('updates existing transactions', function () {
    $cart = CartBuilder::build();

    $order = $cart->createOrder();

    $paymentIntent = Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_CAPTURE');

    $charges = collect($paymentIntent->charges->data);

    $order = app(StoreCharges::class)->store($order, $charges);

    expect($order->transactions)->toHaveCount(1);

    $order = app(StoreCharges::class)->store($order, $charges);

    expect($order->transactions)->toHaveCount(1);

})->group('lunar.stripe.actions');

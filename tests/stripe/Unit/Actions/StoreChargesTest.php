<?php

use Lunar\Stripe\Actions\StoreCharges;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;

uses(TestCase::class);

it('can store successful charge', function () {
    $cart = CartBuilder::build();

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
    expect($transaction->amount)->toBe($charge->amount);
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

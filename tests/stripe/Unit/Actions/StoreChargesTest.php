<?php

uses(\Lunar\Tests\Stripe\Unit\TestCase::class);

it('can store successful charge', function () {
    $cart = \Lunar\Tests\Stripe\Utils\CartBuilder::build();

    $order = $cart->createOrder();

    $paymentIntent = \Lunar\Stripe\Facades\Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_CAPTURE');

    $charges = collect($paymentIntent->charges->data);

    $order = app(\Lunar\Stripe\Actions\StoreCharges::class)->store($order, $charges);

    expect($order->transactions)->toHaveCount(1);

    $charge = $charges->first();
    $transaction = $order->transactions->first();

    expect($transaction->type)->toBe('capture');
    expect($transaction->amount->value)->toBe($charge->amount);
    expect($transaction->reference)->toBe($charge->id);
})->group('lunar.stripe.actions');

it('updates existing transactions', function () {
    $cart = \Lunar\Tests\Stripe\Utils\CartBuilder::build();

    $order = $cart->createOrder();

    $paymentIntent = \Lunar\Stripe\Facades\Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_CAPTURE');

    $charges = collect($paymentIntent->charges->data);

    $order = app(\Lunar\Stripe\Actions\StoreCharges::class)->store($order, $charges);

    expect($order->transactions)->toHaveCount(1);

    $order = app(\Lunar\Stripe\Actions\StoreCharges::class)->store($order, $charges);

    expect($order->transactions)->toHaveCount(1);

})->group('lunar.stripe.actions');

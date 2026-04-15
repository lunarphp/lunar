<?php

use Lunar\Stripe\Actions\UpdateOrderFromIntent;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;

uses(TestCase::class);

it('creates pending transaction when status is requires_action', function () {

    $cart = CartBuilder::build();

    $order = $cart->createOrder();

    $paymentIntent = Stripe::getClient()
        ->paymentIntents
        ->retrieve('PI_REQUIRES_ACTION');

    $updatedOrder = UpdateOrderFromIntent::execute($order, $paymentIntent);
    expect($updatedOrder->status)->toBe($order->status);
    expect($updatedOrder->placed_at)->toBeNull();
})->group('lunar.stripe.actions');

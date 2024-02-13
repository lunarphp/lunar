<?php

uses(\Lunar\Tests\Stripe\Unit\TestCase::class);

it('creates pending transaction when status is requires_action', function () {

    $cart = \Lunar\Tests\Stripe\Utils\CartBuilder::build();

    $order = $cart->createOrder();

    $paymentIntent = \Lunar\Stripe\Facades\StripeFacade::getClient()
        ->paymentIntents
        ->retrieve('PI_REQUIRES_ACTION');

    $updatedOrder = \Lunar\Stripe\Actions\UpdateOrderFromIntent::execute($order, $paymentIntent);

    expect($updatedOrder->status)->toBe($order->status);
    expect($updatedOrder->placed_at)->toBeNull();
})->group('lunar.stripe.actions');

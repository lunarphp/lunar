<?php

use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Models\StripePaymentIntent;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;

uses(TestCase::class);

it('excludes succeeded intents from the active scope', function () {
    $cart = CartBuilder::build();

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_PENDING',
        'status' => 'requires_payment_method',
    ]);

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_DONE',
        'status' => 'succeeded',
    ]);

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_CANCELLED',
        'status' => 'canceled',
    ]);

    $active = $cart->paymentIntents()->active()->pluck('intent_id')->all();

    expect($active)->toBe(['PI_PENDING']);
});

it('does not return a succeeded payment intent from getCartIntentId', function () {
    $cart = CartBuilder::build();

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_DONE',
        'status' => 'succeeded',
    ]);

    expect(Stripe::getCartIntentId($cart))->toBeNull();
});

it('treats a succeeded intent row as inactive via isActive()', function () {
    $intent = new StripePaymentIntent;
    $intent->status = 'succeeded';

    expect($intent->isActive())->toBeFalse();
});

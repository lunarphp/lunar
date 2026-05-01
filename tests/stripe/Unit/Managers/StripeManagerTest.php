<?php

use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Models\StripePaymentIntent;
use Lunar\Tests\Stripe\Unit\TestCase;
use Lunar\Tests\Stripe\Utils\CartBuilder;

use function Pest\Laravel\assertDatabaseHas;

uses(TestCase::class);

it('can create a payment intent', function () {
    $cart = CartBuilder::build();

    $intent = Stripe::createIntent($cart->calculate(), []);

    assertDatabaseHas(StripePaymentIntent::class, [
        'intent_id' => 'pi_1DqH152eZvKYlo2CFHYZuxkP',
        'cart_id' => $cart->id,
        'status' => $intent->status,
    ]);
});

it('returns legacy payment intent id stored in cart meta', function () {
    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => 'PI_LEGACY_META',
        ],
    ]);

    expect(Stripe::getCartIntentId($cart))->toBe('PI_LEGACY_META');
});

it('prefers legacy meta payment intent over active relation', function () {
    $cart = CartBuilder::build([
        'meta' => [
            'payment_intent' => 'PI_LEGACY_META',
        ],
    ]);

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_RELATION',
        'status' => 'requires_payment_method',
    ]);

    expect(Stripe::getCartIntentId($cart))->toBe('PI_LEGACY_META');
});

it('falls back to active payment intent when no legacy meta', function () {
    $cart = CartBuilder::build();

    $cart->paymentIntents()->create([
        'intent_id' => 'PI_RELATION',
        'status' => 'requires_payment_method',
    ]);

    expect(Stripe::getCartIntentId($cart))->toBe('PI_RELATION');
});

<?php

use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stripe\Utils\CartBuilder;

use function Pest\Laravel\assertDatabaseHas;

uses(\Lunar\Tests\Stripe\Unit\TestCase::class);

it('can create a payment intent', function () {
    $cart = CartBuilder::build();

    $intent = Stripe::createIntent($cart->calculate(), []);

    assertDatabaseHas(\Lunar\Stripe\Models\StripePaymentIntent::class, [
        'intent_id' => 'pi_1DqH152eZvKYlo2CFHYZuxkP',
        'cart_id' => $cart->id,
        'status' => $intent->status,
    ]);
});

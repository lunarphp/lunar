<?php

use Lunar\Stripe\Facades\Stripe;
use Lunar\Tests\Stripe\Utils\CartBuilder;

uses(\Lunar\Tests\Stripe\Unit\TestCase::class);

it('can create a payment intent', function () {
    $cart = CartBuilder::build();

    Stripe::createIntent($cart->calculate());

    expect($cart->refresh()->meta['payment_intent'])->toBe('pi_1DqH152eZvKYlo2CFHYZuxkP');
});

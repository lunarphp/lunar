<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\PaymentTerms;
use Lunar\Core\Models\Cart;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('a cart has no payment terms by default', function () {
    expect(Cart::factory()->create()->payment_terms)->toBeNull();
});

test('can set and clear the payment terms on a cart', function () {
    $cart = Cart::factory()->create();

    $cart->setPaymentTerms(PaymentTerms::OnAccount);

    expect($cart->refresh()->payment_terms)->toBe(PaymentTerms::OnAccount);

    $cart->setPaymentTerms(null);

    expect($cart->refresh()->payment_terms)->toBeNull();
});

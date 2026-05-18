<?php

use Lunar\Models\Currency;
use Lunar\Stripe\Facades\Stripe;
use Lunar\Stripe\Managers\StripeManager;
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

it('converts amounts for standard two-decimal currencies', function () {
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(1148, $currency))->toBe(1148);
});

it('converts amounts for zero-decimal currencies', function (string $code) {
    $currency = Currency::factory()->make([
        'code' => $code,
        'decimal_places' => 0,
    ]);

    expect(StripeManager::toStripeAmount(1000, $currency))->toBe(1000);
})->with(['JPY', 'KRW', 'VND']);

it('multiplies amounts by 100 for special zero-decimal currencies', function (string $code) {
    $currency = Currency::factory()->make([
        'code' => $code,
        'decimal_places' => 0,
    ]);

    expect(StripeManager::toStripeAmount(11480, $currency))->toBe(1148000);
})->with(['HUF', 'TWD', 'UGX']);

it('converts amounts for three-decimal currencies', function () {
    $currency = Currency::factory()->make([
        'code' => 'BHD',
        'decimal_places' => 3,
    ]);

    expect(StripeManager::toStripeAmount(10234, $currency))->toBe(10234);
});

it('handles HUF when configured with two decimal places', function () {
    $currency = Currency::factory()->make([
        'code' => 'HUF',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(1148000, $currency))->toBe(1148000);
});

it('handles JPY when configured with two decimal places', function () {
    $currency = Currency::factory()->make([
        'code' => 'JPY',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(100000, $currency))->toBe(1000);
});

it('is case-insensitive on the currency code', function () {
    $currency = Currency::factory()->make([
        'code' => 'huf',
        'decimal_places' => 0,
    ]);

    expect(StripeManager::toStripeAmount(500, $currency))->toBe(50000);
});

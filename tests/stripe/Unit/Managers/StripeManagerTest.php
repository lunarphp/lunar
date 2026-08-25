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

it('passes through amounts for standard currencies', function (string $code, int $decimals, int $value) {
    $currency = Currency::factory()->make([
        'code' => $code,
        'decimal_places' => $decimals,
    ]);

    expect(StripeManager::toStripeAmount($value, $currency))->toBe($value);
})->with([
    ['USD', 2, 1148],
    ['JPY', 0, 1000],
    ['KRW', 0, 1000],
    ['BHD', 3, 10234],
]);

it('multiplies HUF/TWD/UGX amounts by 100 when stored as zero-decimal', function (string $code) {
    $currency = Currency::factory()->make([
        'code' => $code,
        'decimal_places' => 0,
    ]);

    expect(StripeManager::toStripeAmount(11480, $currency))->toBe(1148000);
})->with(['HUF', 'TWD', 'UGX']);

it('passes through HUF when stored with two decimal places', function () {
    $currency = Currency::factory()->make([
        'code' => 'HUF',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(1148000, $currency))->toBe(1148000);
});

it('is case-insensitive on the currency code', function () {
    $currency = Currency::factory()->make([
        'code' => 'huf',
        'decimal_places' => 0,
    ]);

    expect(StripeManager::toStripeAmount(500, $currency))->toBe(50000);
});

it('normalises amounts for currencies configured with more than 2 decimal places', function () {
    // A $60.0000 cart stored with 4 decimal places is 600000, not 6000.
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 4,
    ]);

    expect(StripeManager::toStripeAmount(600000, $currency))->toBe(6000);
});

it('normalises amounts for zero-decimal currencies misconfigured with extra decimal places', function () {
    $currency = Currency::factory()->make([
        'code' => 'JPY',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(100000, $currency))->toBe(1000);
});

it('rounds half-unit boundaries exactly when rescaling', function () {
    // 145 at 3dp is 0.145 — binary float division would misround this to 14.
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 3,
    ]);

    expect(StripeManager::toStripeAmount(145, $currency))->toBe(15);

    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 4,
    ]);

    expect(StripeManager::toStripeAmount(1450, $currency))->toBe(15);
});

it('sends three-decimal currency amounts in thousandths', function () {
    $currency = Currency::factory()->make([
        'code' => 'BHD',
        'decimal_places' => 3,
    ]);

    expect(StripeManager::toStripeAmount(60123, $currency))->toBe(60123);

    $currency = Currency::factory()->make([
        'code' => 'BHD',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::toStripeAmount(6012, $currency))->toBe(60120);
});

it('converts Stripe amounts back to the stored scale', function () {
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 4,
    ]);

    expect(StripeManager::fromStripeAmount(6000, $currency))->toBe(600000);

    $currency = Currency::factory()->make([
        'code' => 'JPY',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::fromStripeAmount(1000, $currency))->toBe(100000);

    $currency = Currency::factory()->make([
        'code' => 'HUF',
        'decimal_places' => 0,
    ]);

    expect(StripeManager::fromStripeAmount(50000, $currency))->toBe(500);

    // Identity for a correctly-configured two-decimal currency.
    $currency = Currency::factory()->make([
        'code' => 'USD',
        'decimal_places' => 2,
    ]);

    expect(StripeManager::fromStripeAmount(1999, $currency))->toBe(1999);
});

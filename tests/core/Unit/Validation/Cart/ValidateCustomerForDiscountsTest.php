<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Discount;
use Lunar\Core\Validation\Cart\ValidateCustomerForDiscounts;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

/**
 * An otherwise complete guest cart with no contact email, carrying the coupon
 * for a 10% discount.
 */
function guestCartWithCoupon(array $discount = []): Cart
{
    $cart = buildCart(['coupon_code' => '10OFF']);

    $cart->addresses()->update(['contact_email' => null]);

    Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Ten percent',
        'coupon' => '10OFF',
        'max_uses_per_user' => 1,
        'data' => ['percentage' => 10],
        ...$discount,
    ]);

    return $cart->refresh();
}

function validateCustomerForDiscounts(Cart $cart): bool
{
    return app(ValidateCustomerForDiscounts::class)->using(cart: $cart)->validate();
}

test('fails when a per-customer-limited discount is applied and the customer is unknown', function () {
    $cart = guestCartWithCoupon();

    expect($cart->calculate()->discountTotal->value)->toBeGreaterThan(0)
        ->and($cart->canCreateOrder())->toBeFalse();

    expect(fn () => validateCustomerForDiscounts($cart))
        ->toThrow(CartException::class, __('lunar::exceptions.carts.customer_email_missing', ['discount' => 'Ten percent']));
});

test('passes once the billing address has an email', function () {
    $cart = guestCartWithCoupon();

    $cart->billingAddress->update(['contact_email' => 'guest@example.com']);

    $cart->refresh();

    expect(validateCustomerForDiscounts($cart))->toBeTrue()
        ->and($cart->canCreateOrder())->toBeTrue();
});

test('passes for a signed-in customer', function () {
    setAuthUserConfig();

    $cart = guestCartWithCoupon();

    $cart->update(['user_id' => User::factory()->create()->id]);

    expect(validateCustomerForDiscounts($cart->refresh()))->toBeTrue();
});

test('passes when the applied discount has no per-customer limit', function () {
    $cart = guestCartWithCoupon(['max_uses_per_user' => null]);

    expect($cart->calculate()->discountTotal->value)->toBeGreaterThan(0)
        ->and(validateCustomerForDiscounts($cart))->toBeTrue();
});

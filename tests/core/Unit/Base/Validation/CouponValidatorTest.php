<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;
use Lunar\Core\Validation\CouponValidator;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can validate coupons', function () {
    $validator = app(CouponValidator::class);

    Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'percentage' => 10,
        ],
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();

    expect($validator->validate('10off'))->toBeTrue();

    expect($validator->validate('10oFf'))->toBeTrue();

    expect($validator->validate('20OFF'))->toBeFalse();
});

test('can validate based on uses', function () {
    $validator = app(CouponValidator::class);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'uses' => 10,
        'max_uses' => 20,
        'coupon' => '10OFF',
        'data' => [
            'percentage' => 10,
        ],
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();

    $discount->update([
        'uses' => 20,
    ]);

    expect($validator->validate('10OFF'))->toBeFalse();

    $discount->update([
        'max_uses' => null,
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();
});

function perUserCoupon(): Discount
{
    return Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'max_uses_per_user' => 1,
        'coupon' => '10OFF',
        'data' => [
            'percentage' => 10,
        ],
    ]);
}

function guestCartWithEmail(string $email): Cart
{
    $cart = Cart::factory()->create();

    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => 'billing',
        'contact_email' => $email,
    ]);

    return $cart->refresh();
}

test('enforces max uses per user for the cart\'s user', function () {
    setAuthUserConfig();

    $validator = app(CouponValidator::class);

    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $discount = perUserCoupon();

    expect($validator->validate('10OFF', $cart))->toBeTrue();

    DiscountRedemption::factory()->for($discount)->create(['user_id' => $user->id]);

    expect($validator->validate('10OFF', $cart))->toBeFalse();
});

test('accepts a per-user-limited coupon for a guest within the limit', function () {
    $discount = perUserCoupon();

    DiscountRedemption::factory()->for($discount)->create(['email' => 'someone-else@example.com']);

    expect(app(CouponValidator::class)->validate('10OFF', guestCartWithEmail('guest@example.com')))->toBeTrue();
});

test('rejects a per-user-limited coupon for a guest whose email has used it', function () {
    $discount = perUserCoupon();

    DiscountRedemption::factory()->for($discount)->create(['email' => 'guest@example.com']);

    expect(app(CouponValidator::class)->validate('10OFF', guestCartWithEmail('Guest@Example.com')))->toBeFalse();
});

test('accepts a per-user-limited coupon for a guest with no email yet', function () {
    $discount = perUserCoupon();

    DiscountRedemption::factory()->for($discount)->create(['email' => 'guest@example.com']);

    expect(app(CouponValidator::class)->validate('10OFF', Cart::factory()->create()))->toBeTrue();
});

test('checks the current session cart by default', function () {
    $discount = perUserCoupon();
    $cart = guestCartWithEmail('guest@example.com');

    CartSession::use($cart);

    expect(Discounts::validateCoupon('10OFF'))->toBeTrue();

    DiscountRedemption::factory()->for($discount)->create(['email' => 'guest@example.com']);

    expect(Discounts::validateCoupon('10OFF'))->toBeFalse()
        ->and(Discounts::validateCoupon('10OFF', guestCartWithEmail('other@example.com')))->toBeTrue();
});

test('allows another user when one is at the per-user limit', function () {
    setAuthUserConfig();

    $validator = app(CouponValidator::class);

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $discount = perUserCoupon();

    DiscountRedemption::factory()->for($discount)->create(['user_id' => $userA->id]);

    expect($validator->validate('10OFF', Cart::factory()->create(['user_id' => $userB->id])))->toBeTrue();
});

test('can validate based on start and end dates', function () {
    $validator = app(CouponValidator::class);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'uses' => 0,
        'max_uses' => null,
        'starts_at' => now()->startOfDay(),
        'ends_at' => now()->endOfWeek(),
        'coupon' => '10OFF',
        'data' => [
            'percentage' => 10,
        ],
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();

    $discount->update([
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subWeek()->endOfWeek(),
    ]);

    expect($validator->validate('10OFF'))->toBeFalse();

    $discount->update([
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subWeek()->endOfWeek(),
    ]);

    expect($validator->validate('10OFF'))->toBeFalse();
});

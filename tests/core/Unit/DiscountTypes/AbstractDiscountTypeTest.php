<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\TestAbstractDiscount;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

beforeEach(function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);
});

test('will handle customer limitation', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $customer = Customer::factory()->create([]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $discountModel = Discount::factory()->coupon('10OFF')->create([
        'type' => TestAbstractDiscount::class,
        'name' => 'Test Coupon',
        'data' => [
            'amounts' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $product = Product::factory()->create();

    $purchasable = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    Price::factory()->create([
        'price' => 1000, // £10
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $discountModel->customers()->attach($customer->id);

    $cart->calculate();

    expect($cart->subTotalDiscounted->value)->toBe(1000);

    $cart->update([
        'customer_id' => $customer->id,
    ]);

    $cart->refresh()->calculate();

    expect($cart->subTotalDiscounted->value)->toBe(900);
});

/**
 * A £10 cart with a 10OFF coupon on a discount limited to one use per user.
 */
function perUserLimitedCart(?int $userId = null): Cart
{
    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => Channel::getDefault()->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
        'user_id' => $userId,
    ]);

    Discount::factory()->coupon('10OFF')->create([
        'type' => TestAbstractDiscount::class,
        'name' => 'Test Coupon',
        'max_uses_per_user' => 1,
        'data' => ['amounts' => ['GBP' => 10]],
    ]);

    $purchasable = ProductVariant::factory()->create(['product_id' => Product::factory()]);

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    return $cart->refresh();
}

function withContactEmail(Cart $cart, string $type, string $email): Cart
{
    CartAddress::factory()->create([
        'cart_id' => $cart->id,
        'type' => $type,
        'contact_email' => $email,
    ]);

    return $cart->refresh();
}

function redeemedBy(array $attributes): DiscountRedemption
{
    return DiscountRedemption::factory()->for(Discount::query()->sole())->create($attributes);
}

test('applies a per-user-limited discount to a guest within the limit', function () {
    $cart = withContactEmail(perUserLimitedCart(), 'billing', 'guest@example.com');

    redeemedBy(['email' => 'someone-else@example.com']);

    expect($cart->calculate()->subTotalDiscounted->value)->toBe(900);
});

test('does not apply a per-user-limited discount to a guest whose email has used it', function () {
    $cart = withContactEmail(perUserLimitedCart(), 'billing', 'Guest@Example.com ');

    redeemedBy(['email' => 'guest@example.com']);

    expect($cart->calculate()->subTotalDiscounted->value)->toBe(1000);
});

test('counts a signed-in customer\'s earlier guest order with the same email', function () {
    setAuthUserConfig();

    $user = User::factory()->create(['email' => 'customer@example.com']);
    $cart = perUserLimitedCart($user->id);

    redeemedBy(['user_id' => null, 'email' => 'customer@example.com']);

    expect($cart->calculate()->subTotalDiscounted->value)->toBe(1000);
});

test('identifies a guest by the billing email over the shipping email', function () {
    $cart = withContactEmail(perUserLimitedCart(), 'shipping', 'shipping@example.com');
    $cart = withContactEmail($cart, 'billing', 'billing@example.com');

    redeemedBy(['email' => 'shipping@example.com']);

    expect($cart->calculate()->subTotalDiscounted->value)->toBe(900);

    redeemedBy(['email' => 'billing@example.com']);

    expect($cart->refresh()->recalculate()->subTotalDiscounted->value)->toBe(1000);
});

test('applies a per-user-limited discount to a user under the limit', function () {
    setAuthUserConfig();

    $user = User::factory()->create();

    expect(perUserLimitedCart($user->id)->calculate()->subTotalDiscounted->value)->toBe(900);
});

test('does not apply a per-user-limited discount to a user at the limit', function () {
    setAuthUserConfig();

    $user = User::factory()->create();
    $cart = perUserLimitedCart($user->id);

    DiscountRedemption::factory()->for(Discount::query()->sole())->create(['user_id' => $user->id]);

    expect($cart->calculate()->subTotalDiscounted->value)->toBe(1000);
});

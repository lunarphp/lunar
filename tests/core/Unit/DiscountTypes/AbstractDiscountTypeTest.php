<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
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

    $discountModel = Discount::factory()->create([
        'type' => TestAbstractDiscount::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
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

test('applies a discount with max uses per user on a guest cart', function () {
    $channel = Channel::getDefault();
    $currency = Currency::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    Discount::factory()->create([
        'type' => TestAbstractDiscount::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'max_uses_per_user' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $purchasable = ProductVariant::factory()->create([
        'product_id' => Product::factory(),
    ]);

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

    $cart->calculate();

    expect($cart->subTotalDiscounted->value)->toBe(900);
});

test('applies a discount when the user is under the per-user limit', function () {
    setAuthUserConfig();

    $channel = Channel::getDefault();
    $currency = Currency::getDefault();
    $user = User::factory()->create();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
        'user_id' => $user->id,
    ]);

    Discount::factory()->create([
        'type' => TestAbstractDiscount::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'max_uses_per_user' => 2,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $purchasable = ProductVariant::factory()->create([
        'product_id' => Product::factory(),
    ]);

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

    $cart->calculate();

    expect($cart->subTotalDiscounted->value)->toBe(900);
});

test('does not apply a discount when the user is at the per-user limit', function () {
    setAuthUserConfig();

    $channel = Channel::getDefault();
    $currency = Currency::getDefault();
    $user = User::factory()->create();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
        'user_id' => $user->id,
    ]);

    $discountModel = Discount::factory()->create([
        'type' => TestAbstractDiscount::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'max_uses_per_user' => 1,
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discountModel->users()->attach($user->id);

    $purchasable = ProductVariant::factory()->create([
        'product_id' => Product::factory(),
    ]);

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

    $cart->calculate();

    expect($cart->subTotalDiscounted->value)->toBe(1000);
});

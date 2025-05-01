<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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

    $customer = \Lunar\Models\Customer::factory()->create([]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $discountModel = Discount::factory()->create([
        'type' => \Lunar\Tests\Core\Stubs\TestAbstractDiscount::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $product = \Lunar\Models\Product::factory()->create();

    $purchasable = \Lunar\Models\ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    \Lunar\Models\Price::factory()->create([
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
})->group('waaaa');

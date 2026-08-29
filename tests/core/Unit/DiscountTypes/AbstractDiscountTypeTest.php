<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\TestAbstractDiscount;
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

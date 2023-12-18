<?php

uses(\Lunar\Tests\TestCase::class);

use Illuminate\Support\Collection;
use Lunar\Base\DataTransferObjects\CartDiscount;
use Lunar\Base\DiscountManagerInterface;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Facades\Discounts;
use Lunar\Managers\DiscountManager;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Stubs\TestDiscountType;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can instantiate manager', function () {
    $manager = app(DiscountManagerInterface::class);
    expect($manager)->toBeInstanceOf(DiscountManager::class);
});

test('can set channel', function () {
    $manager = app(DiscountManagerInterface::class);

    $channel = Channel::factory()->create();

    expect($manager->getChannels())->toHaveCount(0);

    $manager->channel($channel);

    expect($manager->getChannels())->toHaveCount(1);

    $channels = Channel::factory(2)->create();

    $manager->channel($channels);

    expect($manager->getChannels())->toHaveCount(2);

    $this->expectException(InvalidArgumentException::class);

    $manager->channel(Product::factory(2)->create());
});

test('can set customer group', function () {
    $manager = app(DiscountManagerInterface::class);

    $customerGroup = CustomerGroup::factory()->create();

    expect($manager->getCustomerGroups())->toHaveCount(0);

    $manager->customerGroup($customerGroup);

    expect($manager->getCustomerGroups())->toHaveCount(1);

    $customerGroups = CustomerGroup::factory(2)->create();

    $manager->customerGroup($customerGroups);

    expect($manager->getCustomerGroups())->toHaveCount(2);

    $this->expectException(InvalidArgumentException::class);

    $manager->channel(Product::factory(2)->create());
});

test('can restrict discounts to channel', function () {
    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $channelTwo = Channel::factory()->create([
        'default' => false,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $discount = Discount::factory()->create();

    $manager = app(DiscountManagerInterface::class);

    expect($manager->getDiscounts())->toBeEmpty();

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'visible' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
        $channelTwo->id => [
            'enabled' => false,
            'starts_at' => now(),
        ],
    ]);

    expect($manager->getDiscounts())->toHaveCount(1);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->addHour(),
        ],
        $channelTwo->id => [
            'enabled' => false,
            'starts_at' => now(),
        ],
    ]);

    expect($manager->getDiscounts())->toBeEmpty();

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now(),
        ],
        $channelTwo->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    expect($manager->getDiscounts())->toBeEmpty();

    $manager->channel($channelTwo);

    expect($manager->getDiscounts())->toHaveCount(1);
});

test('can restrict discounts to customer group', function () {
    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $customerGroupTwo = CustomerGroup::factory()->create([
        'default' => false,
    ]);

    $discount = Discount::factory()->create();

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'visible' => true,
            'starts_at' => now(),
        ],
    ]);

    $manager = app(DiscountManagerInterface::class);

    expect($manager->getDiscounts())->toHaveCount(1);

    $discount->customerGroups()->sync([
        $channel->id => [
            'visible' => false,
            'enabled' => false,
            'starts_at' => now(),
        ],
    ]);

    expect($manager->getDiscounts())->toBeEmpty();

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'visible' => true,
            'starts_at' => now()->addMinutes(1),
        ],
        $customerGroupTwo->id => [
            'enabled' => false,
            'visible' => false,
            'starts_at' => now()->addMinutes(1),
        ],
    ]);

    $manager->customerGroup($customerGroupTwo);

    expect($manager->getDiscounts())->toBeEmpty();
});

test('can fetch discount types', function () {
    $manager = app(DiscountManagerInterface::class);

    expect($manager->getTypes())->toBeInstanceOf(Collection::class);
});

test('can fetch applied discounts', function () {
    $manager = app(DiscountManagerInterface::class);

    expect($manager->getApplied())->toBeInstanceOf(Collection::class);
    expect($manager->getApplied())->toHaveCount(0);
});

test('can add applied discount', function () {
    $manager = app(DiscountManagerInterface::class);

    expect($manager->getApplied())->toBeInstanceOf(Collection::class);

    expect($manager->getApplied())->toHaveCount(0);

    ProductVariant::factory()->create();

    $discount = Discount::factory()->create();
    $cartLine = CartLine::factory()->create();

    $discount = new CartDiscount(
        model: $cartLine,
        discount: $discount
    );

    $manager->addApplied($discount);

    expect($manager->getApplied())->toHaveCount(1);
});

test('can add new types', function () {
    $manager = app(DiscountManagerInterface::class);

    $testType = $manager->getTypes()->first(function ($type) {
        return get_class($type) == TestDiscountType::class;
    });

    expect($testType)->toBeNull();

    $manager->addType(TestDiscountType::class);

    $testType = $manager->getTypes()->first(function ($type) {
        return get_class($type) == TestDiscountType::class;
    });

    expect($testType)->toBeInstanceOf(TestDiscountType::class);
});

test('can validate coupons', function () {
    $manager = app(DiscountManagerInterface::class);

    Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    expect($manager->validateCoupon('10OFF'))->toBeTrue();

    expect($manager->validateCoupon('20OFF'))->toBeFalse();
});

test('can get discount with coupon', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => null,
    ]);

    $purchasableA = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000, // £10
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasableA),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasableA),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discountA = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Discount A',
        'coupon' => null,
        'starts_at' => now(),
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discountA->channels()->attach([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discountA->customerGroups()->attach([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discountB = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Discount B',
        'coupon' => null,
        'starts_at' => now(),
        'data' => [
            'fixed_value' => true,
            'fixed_values' => [
                'GBP' => 10,
            ],
        ],
    ]);

    $discountB->channels()->attach([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discountB->customerGroups()->attach([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    expect(Discounts::getDiscounts($cart))->toHaveCount(2);

    $discountA->update([
        'coupon' => 'ABCD',
    ]);

    $discountB->update([
        'coupon' => 'ABCDEF',
    ]);

    $cart->update([
        'coupon_code' => 'ABCDEF',
    ]);

    expect(Discounts::getDiscounts($cart->refresh()))->toHaveCount(1);
})->group('moomoo');

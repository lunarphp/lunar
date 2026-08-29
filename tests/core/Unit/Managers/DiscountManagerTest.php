<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\DiscountManager;
use Lunar\Core\DataObjects\CartDiscount;
use Lunar\Core\DiscountTypes\FixedAmountOff;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Managers\DiscountManager as DiscountManagerImpl;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\Stubs\TestDiscountType;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can instantiate manager', function () {
    $manager = app(DiscountManager::class);
    expect($manager)->toBeInstanceOf(DiscountManagerImpl::class);
});

test('can set channel', function () {
    $manager = app(DiscountManager::class);

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
    $manager = app(DiscountManager::class);

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

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => false,
            'starts_at' => null,
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => false,
            'starts_at' => null,
        ],
        $channelTwo->id => [
            'enabled' => false,
            'starts_at' => null,
        ],
    ]);

    $manager = app(DiscountManager::class);

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

    $manager = app(DiscountManager::class);

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
    $manager = app(DiscountManager::class);

    expect($manager->getTypes())->toBeInstanceOf(Collection::class);
});

test('can fetch applied discounts', function () {
    $manager = app(DiscountManager::class);

    expect($manager->getApplied())->toBeInstanceOf(Collection::class);
    expect($manager->getApplied())->toHaveCount(0);
});

test('can add applied discount', function () {
    $manager = app(DiscountManager::class);

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
    $manager = app(DiscountManager::class);

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
    $manager = app(DiscountManager::class);

    Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
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
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasableA->getMorphClass(),
        'priceable_id' => $purchasableA->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 2,
    ]);

    $discountA = Discount::factory()->create([
        'type' => FixedAmountOff::class,
        'name' => 'Test Discount A',
        'coupon' => null,
        'starts_at' => now(),
        'data' => [
            'amounts' => [
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
        'type' => FixedAmountOff::class,
        'name' => 'Test Discount B',
        'coupon' => null,
        'starts_at' => now(),
        'data' => [
            'amounts' => [
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
});

test('stop flag halts further discounts after a discount applies', function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => Currency::getDefault()->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'product_id' => Product::factory(),
    ]);

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => Currency::getDefault()->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $stopper = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Stopper',
        'priority' => 10,
        'stop' => true,
        'data' => [
            'percentage' => 5,
        ],
    ]);

    $shouldNotApply = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Should not apply',
        'priority' => 5,
        'stop' => false,
        'data' => [
            'percentage' => 20,
        ],
    ]);

    foreach ([$stopper, $shouldNotApply] as $discount) {
        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);
    }

    $cart->calculate();

    expect($cart->discounts)->toHaveCount(1);
    expect($cart->discounts->first()->discount->name)->toBe('Stopper');
});

test('stop flag does not halt further discounts when conditions fail', function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => Currency::getDefault()->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'product_id' => Product::factory(),
    ]);

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => Currency::getDefault()->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $couponed = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Coupon discount that wont match',
        'priority' => 10,
        'stop' => true,
        'coupon' => 'WRONG',
        'data' => [
            'percentage' => 20,
        ],
    ]);

    $fallback = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Fallback',
        'priority' => 5,
        'stop' => false,
        'data' => [
            'percentage' => 10,
        ],
    ]);

    foreach ([$couponed, $fallback] as $discount) {
        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);
    }

    $cart->calculate();

    expect($cart->discounts)->toHaveCount(1);
    expect($cart->discounts->first()->discount->name)->toBe('Fallback');
});

test('stop=false discount lets further discounts apply', function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    $channel = Channel::factory()->create([
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => Currency::getDefault()->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'product_id' => Product::factory(),
    ]);

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => Currency::getDefault()->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $first = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'First',
        'priority' => 10,
        'stop' => false,
        'data' => [
            'percentage' => 10,
        ],
    ]);

    $second = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Second',
        'priority' => 5,
        'stop' => false,
        'data' => [
            'percentage' => 20,
        ],
    ]);

    foreach ([$first, $second] as $discount) {
        $discount->customerGroups()->sync([
            $customerGroup->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);

        $discount->channels()->sync([
            $channel->id => [
                'enabled' => true,
                'starts_at' => now(),
            ],
        ]);
    }

    $cart->calculate();

    expect($cart->discounts)->toHaveCount(2);
});

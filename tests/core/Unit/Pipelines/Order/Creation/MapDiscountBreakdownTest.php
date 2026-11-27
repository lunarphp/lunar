<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Pipelines\Order\Creation\CleanUpOrderLines;
use Lunar\Core\Pipelines\Order\Creation\CreateOrderLines;
use Lunar\Core\Pipelines\Order\Creation\MapDiscountBreakdown;
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

test('can map discount with same purchasable with different meta', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $discount = Discount::factory()->coupon('10OFF')->create([
        'type' => PercentageOff::class,
        'name' => 'Test Coupon',
        'data' => [
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
        'meta' => [
            'personalization' => 'Love you mum xxx',
        ],
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
        'meta' => [
            'personalization' => 'Get well soon',
        ],
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    $cart->calculate();

    app(CreateOrderLines::class)->handle($order, function ($order) {
        return $order;
    });

    app(MapDiscountBreakdown::class)->handle($order, function ($order) {
        return $order;
    });

    $appliedDiscount = $order->discount_breakdown->first();

    $appliedDiscountLines = $appliedDiscount->lines->map(function ($line) {
        return $line->line->only('purchasable_type', 'purchasable_id', 'meta');
    });

    $orderLines = $order->lines->map(function ($line) {
        return $line->only('purchasable_type', 'purchasable_id', 'meta');
    });

    expect($appliedDiscountLines)->toHaveCount($orderLines->count());
    expect($appliedDiscountLines->toArray())->toEqual($orderLines->toArray());

    $coupon = $discount->coupons()->sole();
    $coupon->delete();

    expect($order->refresh()->discount_breakdown->first())
        ->coupon_id->toBe($coupon->id)
        ->coupon_code->toBe('10OFF');
});

test('keeps a shopper line and a reward line for the same purchasable apart when a draft order is rebuilt', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $condition = ProductVariant::factory()->create();
    $purchasable = ProductVariant::factory()->create();

    foreach ([$condition, $purchasable] as $variant) {
        Price::factory()->create([
            'price' => 100,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);
    }

    // Three conditions earn three rewards: the shopper's own line takes one,
    // and the discount adds a line for the other two.
    $rewardDiscount = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'data' => ['min_qty' => 1, 'reward_qty' => 1, 'automatically_add_rewards' => true],
    ]);

    $rewardDiscount->customerGroups()->sync([$customerGroup->id => ['enabled' => true, 'starts_at' => now()]]);
    $rewardDiscount->channels()->sync([$channel->id => ['enabled' => true, 'starts_at' => now()->subHour()]]);
    $rewardDiscount->discountableConditions()->create(['discountable_type' => $condition->getMorphClass(), 'discountable_id' => $condition->id]);
    $rewardDiscount->discountableRewards()->create(['discountable_type' => $purchasable->getMorphClass(), 'discountable_id' => $purchasable->id, 'type' => 'reward']);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $condition->getMorphClass(),
        'purchasable_id' => $condition->id,
        'quantity' => 3,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $cart->recalculate();

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    $build = function () use ($order, $cart) {
        // Loaded the way CartSession loads it, with its two variants.
        $order->setRelation('cart', Cart::query()->with(config('lunar.cart.eager_load'))->find($cart->id));

        foreach ([CreateOrderLines::class, CleanUpOrderLines::class, MapDiscountBreakdown::class] as $step) {
            app($step)->handle($order, fn ($order) => $order);
        }

        $order->refresh();
    };

    $build();
    $build();

    $lines = $order->lines->where('purchasable_id', $purchasable->id);

    expect($lines)->toHaveCount(2)
        ->and($lines->whereNull('added_by_discount_id')->first()->quantity)->toBe(1)
        ->and($lines->firstWhere('added_by_discount_id', $rewardDiscount->id)->quantity)->toBe(2);

    $breakdownLineIds = collect($order->discount_breakdown->first()->lines)->pluck('line.id');

    expect($breakdownLineIds->sort()->values()->all())->toBe($lines->pluck('id')->sort()->values()->all());
});

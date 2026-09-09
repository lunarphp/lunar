<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Carts\HydratesCartTotals;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Tests\Core\TestCase;
use Spatie\LaravelBlink\BlinkFacade as Blink;

uses(TestCase::class)->group('carts');

uses(RefreshDatabase::class);

function hydratableCart(): Cart
{
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['code' => 'GBP', 'default' => true]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 2,
    ]);

    $discount = Discount::factory()->create([
        'type' => PercentageOff::class,
        'name' => 'Ten percent',
        'coupon' => 'TENOFF',
        'data' => ['percentage' => 10],
    ]);

    $discount->channels()->sync([$channel->id => ['enabled' => true, 'starts_at' => now()->subHour()]]);
    $discount->customerGroups()->sync([$customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now()->subHour()]]);

    $cart->update(['coupon_code' => 'TENOFF']);

    return $cart->refresh();
}

test('it rebuilds the discount breakdown against the discount and the cart lines', function () {
    $cart = hydratableCart()->calculate();

    expect($cart->discountBreakdown)->toHaveCount(1);

    Blink::flush();

    $hydrated = app(HydratesCartTotals::class)->execute(Cart::query()->find($cart->id));

    expect($hydrated->discountBreakdown)->toHaveCount(1);

    $breakdown = $hydrated->discountBreakdown->first();

    expect($breakdown)->toBeInstanceOf(DiscountBreakdown::class)
        ->and($breakdown->discount->is(Discount::first()))->toBeTrue()
        ->and($breakdown->price->value)->toBe(200)
        ->and($breakdown->lines)->toHaveCount(1)
        ->and($breakdown->lines->first()->line)->toBeInstanceOf(CartLine::class)
        ->and($breakdown->lines->first()->line->is($hydrated->lines->first()))->toBeTrue()
        ->and($breakdown->lines->first()->quantity)->toBe(2)
        ->and($hydrated->discounts)->toHaveCount(1)
        ->and($hydrated->discounts->first())->toBeInstanceOf(PercentageOff::class)
        ->and($hydrated->discounts->first()->discount->is($breakdown->discount))->toBeTrue()
        ->and($hydrated->subTotalDiscounted->value)->toBe(1800)
        ->and($hydrated->lines->first()->subTotalDiscounted->value)->toBe(1800)
        ->and($hydrated->isCalculated())->toBeTrue();
});

test('it drops breakdown lines and discounts that no longer exist', function () {
    $cart = hydratableCart()->calculate();

    Cart::query()->whereKey($cart->id)->toBase()->update([
        'discount_breakdown' => json_encode([
            ['discount_id' => Discount::first()->id, 'total' => 200, 'lines' => [
                ['id' => $cart->lines->first()->id, 'qty' => 2],
                ['id' => 999999, 'qty' => 1],
            ]],
            ['discount_id' => 999999, 'total' => 50, 'lines' => []],
        ]),
    ]);

    Blink::flush();

    $hydrated = app(HydratesCartTotals::class)->execute(Cart::query()->find($cart->id));

    expect($hydrated->discountBreakdown)->toHaveCount(1)
        ->and($hydrated->discountBreakdown->first()->lines)->toHaveCount(1)
        ->and($hydrated->discounts)->toHaveCount(1);
});

test('it gives a cart with no breakdowns empty value objects', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    Cart::query()->whereKey($cart->id)->toBase()->update([
        'sub_total' => 0,
        'total' => 0,
        'calculated_revision' => 0,
        'calculated_at' => now(),
    ]);

    $hydrated = app(HydratesCartTotals::class)->execute(Cart::query()->find($cart->id));

    expect($hydrated->total->value)->toBe(0)
        ->and($hydrated->taxBreakdown)->toBeInstanceOf(TaxBreakdown::class)
        ->and($hydrated->taxBreakdown->amounts)->toBeEmpty()
        ->and($hydrated->shippingBreakdown)->toBeInstanceOf(ShippingBreakdown::class)
        ->and($hydrated->shippingBreakdown->items)->toBeEmpty()
        ->and($hydrated->discountBreakdown)->toBeEmpty()
        ->and($hydrated->discounts)->toBeEmpty()
        ->and($hydrated->freeItems)->toBeEmpty()
        ->and($hydrated->shippingTotal)->toBeNull();
});

test('array access on a cart line prefers the calculated property over the column', function () {
    $cart = hydratableCart()->calculate();

    $line = $cart->lines->first();

    expect($line['total'])->toBe($line->total)
        ->and($cart['total'])->toBe($cart->total)
        ->and($cart->lines->pluck('total')->first())->toBe($line->total)
        ->and($line->getAttribute('total'))->toBe($line->total->value)
        ->and($line->toArray()['total'])->toBe($line->total->value);
});

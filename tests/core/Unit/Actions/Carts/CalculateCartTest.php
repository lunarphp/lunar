<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\DiscountTypes\BuyXGetY;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZonePostcode;
use Lunar\Tests\Core\Stubs\CartPipelineSpy;
use Lunar\Tests\Core\TestCase;
use Spatie\LaravelBlink\BlinkFacade as Blink;

uses(TestCase::class)->group('carts');

uses(RefreshDatabase::class);

beforeEach(function () {
    CartPipelineSpy::reset();

    Config::set('lunar.cart.pipelines.cart', [
        CartPipelineSpy::class,
        ...config('lunar.cart.pipelines.cart'),
    ]);
});

/**
 * A cart exercising every part of the snapshot: two priced lines, a taxed
 * shipping option on the shipping address, and a percentage coupon.
 */
function calculableCart(): Cart
{
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);

    $country = Country::factory()->create();
    $taxClass = TaxClass::factory()->create();
    $taxZone = TaxZone::factory()->create();

    TaxZonePostcode::factory()->create([
        'country_id' => $country->id,
        'tax_zone_id' => $taxZone->id,
        'postcode' => 'SHIPP',
    ]);

    $taxRate = TaxRate::factory()->create(['tax_zone_id' => $taxZone->id]);

    TaxRateAmount::factory()->create([
        'tax_rate_id' => $taxRate->id,
        'tax_class_id' => $taxClass->id,
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $cart->addresses()->createMany([
        CartAddress::factory()->make([
            'type' => 'billing',
            'country_id' => $country->id,
            'postcode' => 'BILL',
        ])->toArray(),
        CartAddress::factory()->make([
            'type' => 'shipping',
            'country_id' => $country->id,
            'postcode' => 'SHIPP',
        ])->toArray(),
    ]);

    ShippingManifest::addOption(new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceValue(500, $currency),
        taxClass: $taxClass,
    ));

    $cart->shippingAddress->update(['shipping_option' => 'BASDEL']);

    foreach ([1000, 2500] as $index => $amount) {
        $variant = ProductVariant::factory()->create(['tax_class_id' => $taxClass->id]);

        Price::factory()->create([
            'price' => $amount,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'quantity' => $index + 1,
        ]);
    }

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

/**
 * Everything the storefront renders from a calculated cart, as plain values.
 */
function calculatedSurface(Cart $cart): array
{
    $line = fn (CartLine $line) => [
        'unitPrice' => $line->unitPrice->value,
        'unitPriceInclTax' => $line->unitPriceInclTax->value,
        'subTotal' => $line->subTotal->value,
        'subTotalDiscounted' => $line->subTotalDiscounted->value,
        'discountTotal' => $line->discountTotal->value,
        'taxAmount' => $line->taxAmount->value,
        'total' => $line->total->value,
        'promotionDescription' => $line->promotionDescription,
        'taxBreakdown' => $line->taxBreakdown->amounts->map(fn ($amount) => [$amount->identifier, $amount->price->value, $amount->percentage])->values()->all(),
    ];

    return [
        'subTotal' => $cart->subTotal->value,
        'subTotalDiscounted' => $cart->subTotalDiscounted->value,
        'discountTotal' => $cart->discountTotal->value,
        'shippingSubTotal' => $cart->shippingSubTotal->value,
        'shippingTaxTotal' => $cart->shippingTaxTotal->value,
        'shippingTotal' => $cart->shippingTotal->value,
        'taxTotal' => $cart->taxTotal->value,
        'total' => $cart->total->value,
        'taxBreakdown' => $cart->taxBreakdown->amounts->map(fn ($amount) => [$amount->identifier, $amount->price->value, $amount->percentage])->values()->all(),
        'shippingBreakdown' => $cart->shippingBreakdown->items->map(fn ($item) => [$item->identifier, $item->name, $item->price->value])->values()->all(),
        'discountBreakdown' => $cart->discountBreakdown->map(fn ($breakdown) => [
            'discount' => $breakdown->discount->id,
            'price' => $breakdown->price->value,
            'lines' => $breakdown->lines->map(fn ($line) => [$line->line->id, $line->quantity])->values()->all(),
        ])->values()->all(),
        'discounts' => $cart->discounts->map(fn ($type) => [$type::class, $type->discount->id])->values()->all(),
        'freeItems' => ($cart->freeItems ?? collect())->map(fn ($item) => [$item->getMorphClass(), $item->getKey()])->values()->all(),
        'lines' => $cart->lines->map($line)->values()->all(),
        'fingerprint' => $cart->fingerprint(),
    ];
}

test('calculating persists the totals snapshot on the cart and its lines', function () {
    $cart = calculableCart();

    $cart->calculate();

    expect(CartPipelineSpy::$runs)->toBe(1)
        ->and($cart->calculated_revision)->toBe($cart->revision)
        ->and($cart->calculated_at->timestamp)->toBe(now()->timestamp);

    $this->assertDatabaseHas((new Cart)->getTable(), [
        'id' => $cart->id,
        'sub_total' => $cart->subTotal->value,
        'sub_total_discounted' => $cart->subTotalDiscounted->value,
        'discount_total' => $cart->discountTotal->value,
        'shipping_sub_total' => $cart->shippingSubTotal->value,
        'shipping_tax_total' => $cart->shippingTaxTotal->value,
        'shipping_total' => $cart->shippingTotal->value,
        'tax_total' => $cart->taxTotal->value,
        'total' => $cart->total->value,
        'revision' => $cart->revision,
        'calculated_revision' => $cart->revision,
    ]);

    $row = Cart::query()->toBase()->find($cart->id);

    expect($row->tax_breakdown)->toBeJson()
        ->and($row->shipping_breakdown)->toBeJson()
        ->and(json_decode($row->discount_breakdown, true))->toHaveCount(1)
        ->and($row->calculated_at)->not->toBeNull();

    foreach ($cart->lines as $line) {
        $this->assertDatabaseHas((new CartLine)->getTable(), [
            'id' => $line->id,
            'unit_price' => $line->unitPrice->value,
            'unit_price_incl_tax' => $line->unitPriceInclTax->value,
            'sub_total' => $line->subTotal->value,
            'sub_total_discounted' => $line->subTotalDiscounted->value,
            'discount_total' => $line->discountTotal->value,
            'tax_total' => $line->taxAmount->value,
            'total' => $line->total->value,
        ]);
    }
});

test('a fresh snapshot is served without running the pipeline and matches the calculated cart', function () {
    $cart = calculableCart();

    $expected = calculatedSurface($cart->calculate());

    expect($expected['discountBreakdown'])->toHaveCount(1)
        ->and($expected['shippingBreakdown'])->toHaveCount(1)
        ->and($expected['taxBreakdown'])->not->toBeEmpty()
        ->and(CartPipelineSpy::$runs)->toBe(1);

    Blink::flush();

    $hydrated = Cart::query()->find($cart->id);

    expect($hydrated->isCalculated())->toBeFalse()
        ->and($hydrated->totalsAreFresh())->toBeTrue();

    $hydrated->calculate();

    expect(CartPipelineSpy::$runs)->toBe(1)
        ->and($hydrated->isCalculated())->toBeTrue()
        ->and(calculatedSurface($hydrated))->toEqual($expected);
});

test('recalculate runs the pipeline while the snapshot is fresh', function () {
    $cart = calculableCart();

    $cart->calculate();
    Blink::flush();

    $reloaded = Cart::query()->find($cart->id);

    $reloaded->recalculate();

    expect(CartPipelineSpy::$runs)->toBe(2);

    $reloaded->calculate(force: true);

    expect(CartPipelineSpy::$runs)->toBe(3);
});

test('a ttl of zero never serves the snapshot but still writes the columns', function () {
    Config::set('lunar.cart.totals.ttl', 0);

    $cart = calculableCart();

    $cart->calculate();

    $row = Cart::query()->toBase()->find($cart->id);

    expect((int) $row->calculated_revision)->toBe((int) $cart->revision)
        ->and((int) $row->total)->toBe($cart->total->value);

    Blink::flush();

    $reloaded = Cart::query()->find($cart->id);

    expect($reloaded->totalsAreFresh())->toBeFalse();

    $reloaded->calculate();

    expect(CartPipelineSpy::$runs)->toBe(2);
});

test('an expired snapshot is recalculated and rewritten without moving the revision', function () {
    $cart = calculableCart();

    $cart->calculate();

    $revision = $cart->revision;

    $this->travel(301)->seconds();
    Blink::flush();

    $reloaded = Cart::query()->find($cart->id);

    expect($reloaded->totalsAreFresh())->toBeFalse();

    $reloaded->calculate();

    expect(CartPipelineSpy::$runs)->toBe(2)
        ->and($reloaded->revision)->toBe($revision)
        ->and($reloaded->calculated_revision)->toBe($revision)
        ->and($reloaded->calculated_at->timestamp)->toBe(now()->timestamp)
        ->and($reloaded->totalsAreFresh())->toBeTrue();
});

test('a snapshot that does not cover every line falls through to the pipeline', function () {
    $cart = calculableCart();

    $cart->calculate();

    // A line added outside the model layer: the cart row is untouched but the
    // line has no totals of its own.
    $variant = $cart->lines->first()->purchasable;

    CartLine::query()->toBase()->insert([
        'public_id' => (string) Str::ulid(),
        'cart_id' => $cart->id,
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Blink::flush();

    $reloaded = Cart::query()->find($cart->id);

    expect($reloaded->totalsAreFresh())->toBeTrue();

    $reloaded->calculate();

    expect(CartPipelineSpy::$runs)->toBe(2)
        ->and($reloaded->lines)->toHaveCount(3)
        ->and($reloaded->lines->every(fn (CartLine $line) => $line->total !== null))->toBeTrue();
});

test('an automatic reward line written during the pipeline does not prevent the snapshot', function () {
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['code' => 'GBP']);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
    ]);

    $productA = Product::factory()->create();
    $productB = Product::factory()->create();

    $purchasableA = ProductVariant::factory()->create(['product_id' => $productA->id]);
    $purchasableB = ProductVariant::factory()->create(['product_id' => $productB->id]);

    foreach ([$purchasableA, $purchasableB] as $purchasable) {
        Price::factory()->create([
            'price' => 1000,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $purchasable->getMorphClass(),
            'priceable_id' => $purchasable->id,
        ]);
    }

    $cart->lines()->create([
        'purchasable_type' => $purchasableA->getMorphClass(),
        'purchasable_id' => $purchasableA->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => BuyXGetY::class,
        'name' => 'Free B with A',
        'data' => [
            'min_qty' => 1,
            'reward_qty' => 1,
            'automatically_add_rewards' => true,
        ],
    ]);

    $discount->customerGroups()->sync([$customerGroup->id => ['enabled' => true, 'starts_at' => now()]]);
    $discount->channels()->sync([$channel->id => ['enabled' => true, 'starts_at' => now()->subHour()]]);

    $discount->discountableConditions()->create([
        'discountable_type' => $productA->getMorphClass(),
        'discountable_id' => $productA->id,
    ]);

    $discount->discountableRewards()->create([
        'discountable_type' => $productB->getMorphClass(),
        'discountable_id' => $productB->id,
        'type' => 'reward',
    ]);

    $cart = $cart->refresh()->calculate();

    expect($cart->lines()->count())->toBe(2)
        ->and($cart->freeItems)->toHaveCount(1)
        ->and($cart->fresh()->calculated_revision)->toBe($cart->fresh()->revision);

    // The reward line is picked up by the next run, and from then on the
    // snapshot covers every line and is served.
    Blink::flush();
    Cart::query()->find($cart->id)->calculate();

    expect(CartPipelineSpy::$runs)->toBe(2);

    Blink::flush();
    $served = Cart::query()->find($cart->id)->calculate();

    expect(CartPipelineSpy::$runs)->toBe(2)
        ->and($served->lines)->toHaveCount(2)
        ->and($served->freeItems)->toHaveCount(1)
        ->and($served->freeItems->first()->is($purchasableB))->toBeTrue();
});

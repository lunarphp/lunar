<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\Discounts;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DiscountTypes\ShippingDiscount;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class);
uses(RefreshDatabase::class);
uses(TestUtils::class);

beforeEach(function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
        'default' => true,
        'enabled' => true,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);
});

function makeShippingBreakdown(Currency $currency, string $methodCode, int $priceValue): ShippingBreakdown
{
    $breakdown = new ShippingBreakdown;
    $breakdown->items->put($methodCode, new ShippingBreakdownItem(
        name: 'Standard Shipping',
        identifier: $methodCode,
        price: new Price($priceValue, $currency, 1),
    ));

    return $breakdown;
}

test('is registered as a discount type', function () {
    $types = Discounts::getTypes();
    expect($types->first(fn ($t) => $t instanceof ShippingDiscount))->toBeInstanceOf(ShippingDiscount::class);
});

test('returns the correct name', function () {
    $discount = new ShippingDiscount;
    expect($discount->getName())->toBeString()->not->toBeEmpty();
});

test('does not apply when conditions are not met (wrong coupon)', function () {
    $currency = Currency::getDefault();

    $cart = $this->createCart($currency, 1000);
    $cart->coupon_code = 'WRONGCODE';
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1000);
    $cart->shippingSubTotal = new Price(1000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'coupon' => 'FREESHIP',
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingSubTotal->value)->toBe(1000);
    expect($cart->discounts ?? collect())->toBeEmpty();
});

test('applies free shipping to any method when no method is specified', function () {
    $currency = Currency::getDefault();

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1000);
    $cart->shippingSubTotal = new Price(1000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingSubTotal->value)->toBe(0);
    expect($cart->shippingBreakdown->items->first()->price->value)->toBe(0);
    expect($cart->discounts)->toHaveCount(1);
    expect($cart->discountBreakdown)->toHaveCount(1);
    expect($cart->discountBreakdown->first()->price->value)->toBe(1000);
});

test('applies configured fixed price (not necessarily free) to shipping', function () {
    $currency = Currency::getDefault();

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'express', 2000);
    $cart->shippingSubTotal = new Price(2000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 500],
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingSubTotal->value)->toBe(500);
    expect($cart->discountBreakdown->first()->price->value)->toBe(1500);
});

test('applies only to the matching shipping method', function () {
    $currency = Currency::getDefault();

    ShippingZone::factory()->create(['type' => 'countries']);

    $standardMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'code' => 'standard',
        'data' => ['charge_by' => 'cart_total'],
    ]);

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1000);
    $cart->shippingSubTotal = new Price(1000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => $standardMethod->id,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingSubTotal->value)->toBe(0);
    expect($cart->discounts)->toHaveCount(1);
});

test('does not apply when cart uses a different shipping method than configured', function () {
    $currency = Currency::getDefault();

    $expressMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'code' => 'express',
        'data' => ['charge_by' => 'cart_total'],
    ]);

    $cart = $this->createCart($currency, 1000);
    // Cart has 'standard' in breakdown, but discount is for 'express'
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1000);
    $cart->shippingSubTotal = new Price(1000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => $expressMethod->id,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    // Nothing should change
    expect($cart->shippingSubTotal->value)->toBe(1000);
    expect($cart->discounts ?? collect())->toBeEmpty();
});

test('does not apply when there is no shipping breakdown', function () {
    $currency = Currency::getDefault();

    $cart = $this->createCart($currency, 1000);
    // No shippingBreakdown set

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->discounts ?? collect())->toBeEmpty();
});

test('does not add to discount breakdown when price is already zero', function () {
    $currency = Currency::getDefault();

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 0);
    $cart->shippingSubTotal = new Price(0, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    // Discount is applied (pushed to discounts) but saving is 0, so no breakdown entry
    expect($cart->discountBreakdown ?? collect())->toBeEmpty();
});

test('does not apply when currency has no configured price', function () {
    $currency = Currency::getDefault(); // GBP

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1000);
    $cart->shippingSubTotal = new Price(1000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'fixed',
                    'prices' => ['USD' => 0], // configured for USD, cart is GBP
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingSubTotal->value)->toBe(1000);
    expect($cart->discounts ?? collect())->toBeEmpty();
});

test('applies percentage discount to any shipping method', function () {
    $currency = Currency::getDefault();

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1000);
    $cart->shippingSubTotal = new Price(1000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'percentage',
                    'percentage' => 50,
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingSubTotal->value)->toBe(500);
    expect($cart->discountBreakdown->first()->price->value)->toBe(500);
    expect($cart->discounts)->toHaveCount(1);
});

test('applies 100% percentage discount makes shipping free', function () {
    $currency = Currency::getDefault();

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1200);
    $cart->shippingSubTotal = new Price(1200, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'percentage',
                    'percentage' => 100,
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingSubTotal->value)->toBe(0);
});

test('applies different rules to different shipping methods', function () {
    $currency = Currency::getDefault();

    $standardMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'code' => 'standard',
        'data' => ['charge_by' => 'cart_total'],
    ]);

    $expressMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'code' => 'express',
        'data' => ['charge_by' => 'cart_total'],
    ]);

    $breakdown = new ShippingBreakdown;
    $breakdown->items->put('standard', new ShippingBreakdownItem(
        name: 'Standard Shipping',
        identifier: 'standard',
        price: new Price(1000, $currency, 1),
    ));
    $breakdown->items->put('express', new ShippingBreakdownItem(
        name: 'Express Shipping',
        identifier: 'express',
        price: new Price(2000, $currency, 1),
    ));

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = $breakdown;
    $cart->shippingSubTotal = new Price(3000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => $standardMethod->id,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
                [
                    'shipping_method_id' => $expressMethod->id,
                    'type' => 'percentage',
                    'percentage' => 50,
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    expect($cart->shippingBreakdown->items->get('standard')->price->value)->toBe(0);
    expect($cart->shippingBreakdown->items->get('express')->price->value)->toBe(1000);
    expect($cart->shippingSubTotal->value)->toBe(1000);
    expect($cart->discountBreakdown->first()->price->value)->toBe(2000);
});

test('specific method rule takes priority over catch-all rule', function () {
    $currency = Currency::getDefault();

    $standardMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'code' => 'standard',
        'data' => ['charge_by' => 'cart_total'],
    ]);

    $cart = $this->createCart($currency, 1000);
    $cart->shippingBreakdown = makeShippingBreakdown($currency, 'standard', 1000);
    $cart->shippingSubTotal = new Price(1000, $currency, 1);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'percentage',
                    'percentage' => 10, // catch-all: 10% off
                ],
                [
                    'shipping_method_id' => $standardMethod->id,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0], // specific: free
                ],
            ],
        ],
    ]);

    $discount = new ShippingDiscount;
    $discount->with($discountModel);
    $discount->apply($cart);

    // Should use the specific rule (free), not the catch-all (10% off)
    expect($cart->shippingSubTotal->value)->toBe(0);
});

test('applies correctly through full cart calculation pipeline', function () {
    $currency = Currency::getDefault();
    $channel = Channel::getDefault();

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'cart_total'],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
    ]);

    $discountModel = Discount::factory()->create([
        'type' => ShippingDiscount::class,
        'coupon' => 'FREESHIP',
        'data' => [
            'methods' => [
                [
                    'shipping_method_id' => null,
                    'type' => 'fixed',
                    'prices' => ['GBP' => 0],
                ],
            ],
            'min_prices' => [],
        ],
    ]);

    $discountModel->channels()->attach($channel, ['enabled' => true, 'starts_at' => null]);
    $discountModel->customerGroups()->attach(CustomerGroup::getDefault(), [
        'enabled' => true,
        'visible' => true,
        'starts_at' => null,
        'ends_at' => null,
    ]);

    $cart = $this->createCart($currency, 1000, calculate: false);
    $cart->coupon_code = 'FREESHIP';

    // Manually set the shipping option on the cart
    $cart->shippingOptionOverride = new ShippingOption(
        name: $shippingMethod->name,
        description: '',
        identifier: $shippingMethod->code,
        price: new Price(1000, $currency, 1),
        taxClass: TaxClass::getDefault(),
    );

    $cart->calculate();

    expect($cart->shippingSubTotal->value)->toBe(0);
    expect($cart->discounts)->toHaveCount(1);
});

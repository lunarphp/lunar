<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Shipping\Contracts\Actions\ShippingRates\DeletesShippingRate;
use Lunar\Shipping\Contracts\Actions\ShippingRates\SavesShippingRate;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class, RefreshDatabase::class)->group('shipping', 'shipping-actions');

beforeEach(function () {
    $this->gbp = Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2, 'default' => true]);
    $this->jpy = Currency::factory()->create(['code' => 'JPY', 'decimal_places' => 0, 'default' => false]);
    $this->zone = ShippingZone::factory()->create();
});

test('a rate is created with base prices scaled through each currency decimal places', function () {
    $method = ShippingMethod::factory()->create(['data' => ['charge_by' => 'cart_total']]);

    $rate = app(SavesShippingRate::class)->execute($this->zone, null, [
        'shipping_method_id' => $method->id,
        'enabled' => true,
        'base_prices' => ['GBP' => '5.50', 'JPY' => 500],
        'tiers' => [],
    ]);

    $prices = $rate->basePrices()->get()->keyBy('currency_id');

    expect($rate->shipping_zone_id)->toBe($this->zone->id)
        ->and($prices[$this->gbp->id]->price)->toBe(550)
        ->and($prices[$this->jpy->id]->price)->toBe(500);
});

test('cart-total tiers scale the minimum spend, weight tiers store it raw', function () {
    $group = CustomerGroup::factory()->create();
    $bySpend = ShippingMethod::factory()->create(['data' => ['charge_by' => 'cart_total']]);
    $byWeight = ShippingMethod::factory()->create(['data' => ['charge_by' => 'weight'], 'weight_unit' => 'kg']);

    $spendRate = app(SavesShippingRate::class)->execute($this->zone, null, [
        'shipping_method_id' => $bySpend->id,
        'base_prices' => ['GBP' => 4],
        'tiers' => [['customer_group_id' => $group->id, 'currency_code' => 'GBP', 'min_quantity' => '20.00', 'price' => '2.50']],
    ]);

    $weightRate = app(SavesShippingRate::class)->execute($this->zone, null, [
        'shipping_method_id' => $byWeight->id,
        'base_prices' => ['GBP' => 4],
        'tiers' => [['currency_code' => 'GBP', 'min_quantity' => 5, 'price' => '9.99']],
    ]);

    $spendTier = $spendRate->priceBreaks()->first();
    $weightTier = $weightRate->priceBreaks()->first();

    expect($spendTier->min_quantity)->toBe(2000)
        ->and($spendTier->price)->toBe(250)
        ->and($spendTier->customer_group_id)->toBe($group->id)
        ->and($weightTier->min_quantity)->toBe(5)
        ->and($weightTier->price)->toBe(999);
});

test('saving an existing rate replaces its prices and can disable it', function () {
    $method = ShippingMethod::factory()->create(['data' => ['charge_by' => 'cart_total']]);
    $rate = app(SavesShippingRate::class)->execute($this->zone, null, [
        'shipping_method_id' => $method->id,
        'base_prices' => ['GBP' => 4, 'JPY' => 400],
        'tiers' => [['currency_code' => 'GBP', 'min_quantity' => 10, 'price' => 1]],
    ]);

    app(SavesShippingRate::class)->execute($this->zone, $rate, [
        'shipping_method_id' => $method->id,
        'enabled' => false,
        'base_prices' => ['GBP' => 6, 'JPY' => null],
        'tiers' => [],
    ]);

    $rate->refresh();

    expect($rate->enabled)->toBeFalsy()
        ->and($rate->basePrices()->count())->toBe(1)
        ->and($rate->basePrices()->first()->price)->toBe(600)
        ->and($rate->priceBreaks()->count())->toBe(0);
});

test('deleting a rate removes its prices', function () {
    $method = ShippingMethod::factory()->create();
    $rate = app(SavesShippingRate::class)->execute($this->zone, null, [
        'shipping_method_id' => $method->id,
        'base_prices' => ['GBP' => 4],
    ]);

    app(DeletesShippingRate::class)->execute($rate);

    expect($this->zone->rates()->count())->toBe(0);
});

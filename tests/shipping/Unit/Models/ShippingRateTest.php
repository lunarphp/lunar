<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class);
uses(RefreshDatabase::class);
uses(TestUtils::class);

function makeShippingRate(Currency $currency): ShippingRate
{
    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'flat-rate',
        'data' => ['charge' => ["{$currency->code}" => 500]],
    ]);

    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $shippingMethod->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->create([
        'price' => 500,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
    ]);

    return $shippingRate;
}

test('getTaxClass returns the default tax class when config is default', function () {
    $currency = Currency::factory()->create(['default' => true]);
    $defaultTaxClass = TaxClass::factory()->create(['default' => true]);

    Config::set('lunar.shipping-tables.shipping_rate_tax_calculation', 'default');

    $shippingRate = makeShippingRate($currency);
    $cart = $this->createCart($currency, calculate: false);

    $shippingRate->getShippingOption($cart);

    expect($shippingRate->getTaxClass()->id)->toBe($defaultTaxClass->id);
})->group('shipping-rate');

test('getTaxClass uses callable config to resolve tax class', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    $customTaxClass = TaxClass::factory()->create(['name' => 'Custom Tax Class']);

    Config::set('lunar.shipping-tables.shipping_rate_tax_calculation', function (Cart $cart) use ($customTaxClass) {
        return $customTaxClass;
    });

    $shippingRate = makeShippingRate($currency);
    $cart = $this->createCart($currency, calculate: false);

    $shippingRate->getShippingOption($cart);

    expect($shippingRate->getTaxClass()->id)->toBe($customTaxClass->id);
})->group('shipping-rate');

test('callable config receives the cart instance', function () {
    $currency = Currency::factory()->create(['default' => true]);
    $defaultTaxClass = TaxClass::factory()->create(['default' => true]);

    $receivedCart = null;

    Config::set('lunar.shipping-tables.shipping_rate_tax_calculation', function (Cart $cart) use (&$receivedCart, $defaultTaxClass) {
        $receivedCart = $cart;

        return $defaultTaxClass;
    });

    $shippingRate = makeShippingRate($currency);
    $cart = $this->createCart($currency, calculate: false);

    $shippingRate->getShippingOption($cart);

    expect($receivedCart)->not->toBeNull()
        ->and($receivedCart->id)->toBe($cart->id);
})->group('shipping-rate');

test('getTaxClass uses an invokable class as callable config', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    $customTaxClass = TaxClass::factory()->create(['name' => 'Invokable Tax Class']);

    $resolver = new class($customTaxClass)
    {
        public function __construct(private TaxClass $taxClass) {}

        public function __invoke(Cart $cart): TaxClass
        {
            return $this->taxClass;
        }
    };

    Config::set('lunar.shipping-tables.shipping_rate_tax_calculation', $resolver);

    $shippingRate = makeShippingRate($currency);
    $cart = $this->createCart($currency, calculate: false);

    $shippingRate->getShippingOption($cart);

    expect($shippingRate->getTaxClass()->id)->toBe($customTaxClass->id);
})->group('shipping-rate');

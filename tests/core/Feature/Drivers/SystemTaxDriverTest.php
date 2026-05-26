<?php

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Drivers\SystemTaxDriver;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRate;
use Lunar\Core\Models\TaxRateAmount;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class)->group('taxes');

test('can set shipping address', function () {
    $address = Address::factory()->create();

    $driver = app(SystemTaxDriver::class)
        ->setShippingAddress($address);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('can set billing address', function () {
    $address = Address::factory()->create();

    $driver = app(SystemTaxDriver::class)
        ->setBillingAddress($address);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('must set valid address', function () {
    $this->expectException(TypeError::class);

    $driver = app(SystemTaxDriver::class)
        ->setShippingAddress('ddd');

    $driver = app(SystemTaxDriver::class)
        ->setBillingAddress('ddd');
});

test('can set currency', function () {
    $currency = Currency::factory()->create();

    $driver = app(SystemTaxDriver::class)
        ->setCurrency($currency);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('must set valid currency', function () {
    $this->expectException(TypeError::class);

    $driver = app(SystemTaxDriver::class)
        ->setCurrency('ddd');
});

test('can set purchasable', function () {
    $variant = ProductVariant::factory()->create();

    $driver = app(SystemTaxDriver::class)
        ->setPurchasable($variant);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('can set cart line', function () {
    $line = CartLine::factory()->create();

    $driver = app(SystemTaxDriver::class)
        ->setCartLine($line);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('can get breakdown', function () {
    $address = Address::factory()->create();
    $currency = Currency::factory()->create();
    $variant = ProductVariant::factory()->create();
    $line = CartLine::factory()->create([
        'purchasable_id' => $variant->id,
    ]);
    $subTotal = 833;

    // 8.33 in decimal
    $breakdown = app(SystemTaxDriver::class)
        ->setShippingAddress($address)
        ->setBillingAddress($address)
        ->setCurrency($currency)
        ->setPurchasable($variant)
        ->setCartLine($line)
        ->getBreakdown($subTotal);

    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($breakdown->amounts[0]->price->value)->toEqual(167);
});

test('can get breakdown price inc', function () {
    Config::set('lunar.pricing.stored_inclusive_of_tax', true);

    $address = Address::factory()->create();
    $currency = Currency::factory()->create();
    $line = CartLine::factory()->create();
    $subTotal = 999;

    $breakdown = app(SystemTaxDriver::class)
        ->setShippingAddress($address)
        ->setBillingAddress($address)
        ->setCurrency($currency)
        ->setPurchasable($line->purchasable)
        ->setCartLine($line)
        ->getBreakdown($subTotal);

    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);
    // withoutTax(999, 0.20) = 832 (largest x where withTax(x) <= 999); tax = 999 - 832 = 167.
    expect($breakdown->amounts[0]->price->value)->toEqual(167);
});

test('can set tax zone', function () {
    $taxZone = TaxZone::factory()->create();

    $driver = app(SystemTaxDriver::class)
        ->setTaxZone($taxZone);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('uses cart tax zone override instead of default zone', function () {
    $address = Address::factory()->create();
    $currency = Currency::factory()->create();
    $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create();
    $overrideTaxZone = TaxZone::factory()->state(['default' => false])->create();

    $taxClass = TaxClass::factory()->create();

    // Default zone: 20 %
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])->create()->id,
        'percentage' => 20,
    ]);

    // Override zone: 5 %
    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $overrideTaxZone])->create()->id,
        'percentage' => 5,
    ]);

    $variant = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();
    $line = CartLine::factory(['purchasable_id' => $variant->id])->create();

    $breakdown = app(SystemTaxDriver::class)
        ->setShippingAddress($address)
        ->setBillingAddress($address)
        ->setCurrency($currency)
        ->setPurchasable($variant)
        ->setCartLine($line)
        ->setTaxZone($overrideTaxZone)
        ->getBreakdown(1000);

    // 5 % of 1000 = 50, not 20 % = 200
    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($breakdown->amounts->count())->toEqual(1);
    expect($breakdown->amounts[0]->price->value)->toEqual(50);
});

test('falls back to address-derived zone when no override is set', function () {
    $address = Address::factory()->create();
    $currency = Currency::factory()->create();
    $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create();

    $taxClass = TaxClass::factory()->create();

    TaxRateAmount::factory()->create([
        'tax_class_id' => $taxClass->id,
        'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])->create()->id,
        'percentage' => 20,
    ]);

    $variant = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();
    $line = CartLine::factory(['purchasable_id' => $variant->id])->create();

    // No setTaxZone() call
    $breakdown = app(SystemTaxDriver::class)
        ->setShippingAddress($address)
        ->setBillingAddress($address)
        ->setCurrency($currency)
        ->setPurchasable($variant)
        ->setCartLine($line)
        ->getBreakdown(1000);

    // Should use address-derived (default) zone → 20 %
    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($breakdown->amounts->count())->toEqual(1);
    expect($breakdown->amounts[0]->price->value)->toEqual(200);
});

test('can get breakdown with correct tax zone', function () {
    $address = Address::factory()->create();
    $currency = Currency::factory()->create();

    $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create();
    $nonDefaultTaxZone1 = TaxZone::factory()->state(['default' => false])->create();
    $nonDefaultTaxZone2 = TaxZone::factory()->state(['default' => false])->create();

    $taxClass = TaxClass::factory()->has(
        TaxRateAmount::factory()
            ->count(4)
            ->state(new Sequence(
                ['percentage' => 10, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])],
                ['percentage' => 15, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])],
                ['percentage' => 20, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $nonDefaultTaxZone1])],
                ['percentage' => 25, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $nonDefaultTaxZone2])],
            ))
    )->create();

    $variant = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();
    $line = CartLine::factory(['purchasable_id' => $variant->id])->create();
    $subTotal = 1000;

    // 10.00 in decimal
    $breakdown = app(SystemTaxDriver::class)
        ->setShippingAddress($address)
        ->setBillingAddress($address)
        ->setCurrency($currency)
        ->setPurchasable($variant)
        ->setCartLine($line)
        ->getBreakdown($subTotal);

    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);

    // Only the 2 tax rates from the default tax zone should have been applied
    expect($breakdown->amounts->count())->toEqual(2);

    expect($breakdown->amounts[0]->price->value)->toEqual(100);
    expect($breakdown->amounts[1]->price->value)->toEqual(150);
});

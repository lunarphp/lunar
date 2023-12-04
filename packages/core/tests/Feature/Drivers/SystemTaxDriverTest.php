<?php

uses(\Lunar\Tests\TestCase::class);
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Config;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Drivers\SystemTaxDriver;
use Lunar\Models\Address;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can set shipping address', function () {
    $address = Address::factory()->create();

    $driver = (new SystemTaxDriver)
        ->setShippingAddress($address);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('can set billing address', function () {
    $address = Address::factory()->create();

    $driver = (new SystemTaxDriver)
        ->setBillingAddress($address);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('must set valid address', function () {
    $this->expectException(\TypeError::class);

    $driver = (new SystemTaxDriver)
        ->setShippingAddress('ddd');

    $driver = (new SystemTaxDriver)
        ->setBillingAddress('ddd');
});

test('can set currency', function () {
    $currency = Currency::factory()->create();

    $driver = (new SystemTaxDriver)
        ->setCurrency($currency);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('must set valid currency', function () {
    $this->expectException(\TypeError::class);

    $driver = (new SystemTaxDriver)
        ->setCurrency('ddd');
});

test('can set purchasable', function () {
    $variant = ProductVariant::factory()->create();

    $driver = (new SystemTaxDriver)
        ->setPurchasable($variant);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('can set cart line', function () {
    CartLine::unsetEventDispatcher();

    $line = CartLine::factory()->create();

    $driver = (new SystemTaxDriver)
        ->setCartLine($line);

    expect($driver)->toBeInstanceOf(SystemTaxDriver::class);
});

test('can get breakdown', function () {
    $address = Address::factory()->create();
    $currency = Currency::factory()->create();
    $variant = ProductVariant::factory()->create();
    $line = CartLine::factory()->create();
    $subTotal = 833;

    // 8.33 in decimal
    $breakdown = (new SystemTaxDriver)
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

    $breakdown = (new SystemTaxDriver)
        ->setShippingAddress($address)
        ->setBillingAddress($address)
        ->setCurrency($currency)
        ->setPurchasable($line->purchasable)
        ->setCartLine($line)
        ->getBreakdown($subTotal);

    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);
    expect($breakdown->amounts[0]->price->value)->toEqual(166);
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
    $breakdown = (new SystemTaxDriver)
        ->setShippingAddress($address)
        ->setBillingAddress($address)
        ->setCurrency($currency)
        ->setPurchasable($variant)
        ->setCartLine($line)
        ->getBreakdown($subTotal);

    expect($breakdown)->toBeInstanceOf(TaxBreakdown::class);

    //Only the 2 tax rates from the default tax zone should have been applied
    expect($breakdown->amounts->count())->toEqual(2);

    expect($breakdown->amounts[0]->price->value)->toEqual(100);
    expect($breakdown->amounts[1]->price->value)->toEqual(150);
});

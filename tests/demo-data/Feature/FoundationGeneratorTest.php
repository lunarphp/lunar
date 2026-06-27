<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxZone;
use Lunar\DemoData\Generators\FoundationGenerator;
use Lunar\DemoData\Support\DemoContext;
use Lunar\Tests\DemoData\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function generateFoundation(): DemoContext
{
    $context = DemoContext::fromConfig('small');

    app(FoundationGenerator::class)->generate($context);

    return $context;
}

test('it seeds the store foundation', function () {
    $context = generateFoundation();

    expect(Language::whereCode('en')->exists())->toBeTrue();
    expect(Channel::whereHandle('webstore')->whereDefault(true)->exists())->toBeTrue();
    expect(CustomerGroup::whereHandle('retail')->whereDefault(true)->exists())->toBeTrue();
    expect(Location::whereHandle('default')->whereDefault(true)->exists())->toBeTrue();

    expect($context->get('channel'))->toBeInstanceOf(Channel::class);
    expect($context->get('location'))->toBeInstanceOf(Location::class);
});

test('it seeds the configured currencies with a single default', function () {
    generateFoundation();

    expect(Currency::whereCode('GBP')->exists())->toBeTrue();
    expect(Currency::whereCode('USD')->exists())->toBeTrue();
    expect(Currency::whereCode('EUR')->exists())->toBeTrue();

    expect(Currency::whereDefault(true)->count())->toBe(1);
    expect(Currency::whereDefault(true)->first()->code)->toBe('GBP');
    expect((float) Currency::whereCode('GBP')->first()->exchange_rate)->toBe(1.0);
});

test('it seeds a tax class, zone and a 20% rate', function () {
    generateFoundation();

    $taxClass = TaxClass::whereName('Default Tax Class')->first();
    $zone = TaxZone::whereName('Default Tax Zone')->first();

    expect($taxClass)->not->toBeNull();
    expect($zone)->not->toBeNull();
    expect($zone->default)->toBeTrue();

    $amount = $zone->taxRates()->first()->taxRateAmounts()->first();
    expect((int) $amount->percentage)->toBe(20);
    expect($amount->tax_class_id)->toBe($taxClass->id);
});

test('it is idempotent', function () {
    generateFoundation();
    generateFoundation();

    expect(Currency::whereCode('GBP')->count())->toBe(1);
    expect(Channel::whereHandle('webstore')->count())->toBe(1);
    expect(TaxZone::whereName('Default Tax Zone')->count())->toBe(1);
    expect(Location::whereHandle('default')->count())->toBe(1);
});

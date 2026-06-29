<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Core\Models\TaxZone;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('a region resolves its channel, currency, language and tax zone', function () {
    $channel = Channel::factory()->create(['default' => true]);
    $currency = Currency::factory()->create(['default' => true]);
    $language = Language::factory()->create(['default' => true]);
    $taxZone = TaxZone::factory()->create(['default' => true]);

    $region = Region::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'language_id' => $language->id,
        'tax_zone_id' => $taxZone->id,
    ]);

    expect($region->channel->id)->toBe($channel->id);
    expect($region->currency->id)->toBe($currency->id);
    expect($region->language->id)->toBe($language->id);
    expect($region->taxZone->id)->toBe($taxZone->id);
});

test('a region exposes the default record', function () {
    Region::factory()->create(['default' => false]);
    $default = Region::factory()->create(['default' => true]);

    expect(Region::getDefault()->id)->toBe($default->id);
});

test('a region serves a set of countries through the pivot', function () {
    $region = Region::factory()->create();
    $countries = Country::factory(3)->create();

    $region->countries()->sync($countries->pluck('id'));

    expect($region->countries()->count())->toBe(3);
});

test('prices_inc_tax casts to a nullable boolean', function () {
    $region = Region::factory()->create(['prices_inc_tax' => null]);
    expect($region->prices_inc_tax)->toBeNull();

    $region->update(['prices_inc_tax' => 1]);
    expect($region->refresh()->prices_inc_tax)->toBeTrue();
});

test('the handle is stored as a slug', function () {
    $region = Region::factory()->create(['handle' => 'United Kingdom']);

    expect($region->handle)->toBe('united-kingdom');
});

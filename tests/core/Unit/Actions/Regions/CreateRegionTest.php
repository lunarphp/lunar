<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Regions\CreateRegion;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Region;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a region with the given attributes', function () {
    $region = app(CreateRegion::class)->execute([
        'name' => 'Europe',
        'handle' => 'europe',
        'channel_id' => Channel::factory()->create()->id,
        'currency_id' => Currency::factory()->create()->id,
        'language_id' => Language::factory()->create()->id,
    ]);

    expect($region)->toBeInstanceOf(Region::class);

    $this->assertDatabaseHas('lunar_regions', [
        'id' => $region->id,
        'name' => 'Europe',
        'handle' => 'europe',
    ]);
});

test('attaches the given countries', function () {
    $countries = Country::factory(2)->create();

    $region = app(CreateRegion::class)->execute([
        'name' => 'Europe',
        'handle' => 'europe',
        'channel_id' => Channel::factory()->create()->id,
        'currency_id' => Currency::factory()->create()->id,
        'language_id' => Language::factory()->create()->id,
        'countries' => $countries->pluck('id')->all(),
    ]);

    expect($region->countries()->count())->toBe(2);
});

test('demotes the previous default when created as default', function () {
    $previous = Region::factory()->create(['default' => true]);

    $region = app(CreateRegion::class)->execute([
        'name' => 'Europe',
        'handle' => 'europe',
        'channel_id' => Channel::factory()->create()->id,
        'currency_id' => Currency::factory()->create()->id,
        'language_id' => Language::factory()->create()->id,
        'default' => true,
    ]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($region->refresh()->default)->toBeTrue();
});

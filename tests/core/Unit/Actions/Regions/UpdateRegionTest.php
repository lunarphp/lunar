<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Regions\UpdateRegion;
use Lunar\Core\Exceptions\RegionActionException;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Region;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the region attributes', function () {
    $region = Region::factory()->create(['name' => 'Old Name', 'default' => false]);

    app(UpdateRegion::class)->execute($region, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_regions', [
        'id' => $region->id,
        'name' => 'New Name',
    ]);
});

test('replaces the country coverage when supplied', function () {
    $region = Region::factory()->create();
    $original = Country::factory()->create();
    $region->countries()->sync([$original->id]);

    $replacement = Country::factory()->create();

    app(UpdateRegion::class)->execute($region, ['countries' => [$replacement->id]]);

    expect($region->countries()->pluck('country_id')->all())->toBe([$replacement->id]);
});

test('keeps the country coverage when none is supplied', function () {
    $region = Region::factory()->create();
    $region->countries()->sync([Country::factory()->create()->id]);

    app(UpdateRegion::class)->execute($region, ['name' => 'Renamed']);

    expect($region->countries()->count())->toBe(1);
});

test('promoting to default demotes the previous default', function () {
    $previous = Region::factory()->create(['default' => true]);
    $region = Region::factory()->create(['default' => false]);

    app(UpdateRegion::class)->execute($region, ['default' => true]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($region->refresh()->default)->toBeTrue();
});

test('refuses to unset the default flag directly', function () {
    $region = Region::factory()->create(['default' => true]);

    expect(fn () => app(UpdateRegion::class)->execute($region, ['default' => false]))
        ->toThrow(RegionActionException::class);
});

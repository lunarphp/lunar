<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Regions\DeleteRegion;
use Lunar\Core\Exceptions\RegionActionException;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Region;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a region and detaches its countries', function () {
    $region = Region::factory()->create(['default' => false]);
    $region->countries()->sync([Country::factory()->create()->id]);

    app(DeleteRegion::class)->execute($region);

    $this->assertDatabaseMissing('lunar_regions', ['id' => $region->id]);
    $this->assertDatabaseMissing('lunar_country_region', ['region_id' => $region->id]);
});

test('refuses to delete the default region', function () {
    $region = Region::factory()->create(['default' => true]);

    expect(fn () => app(DeleteRegion::class)->execute($region))
        ->toThrow(RegionActionException::class);
});

test('refuses to delete a region with order history', function () {
    $region = Region::factory()->create(['default' => false]);
    Order::factory()->create(['region_id' => $region->id]);

    expect(fn () => app(DeleteRegion::class)->execute($region))
        ->toThrow(RegionActionException::class);

    $this->assertDatabaseHas('lunar_regions', ['id' => $region->id]);
});

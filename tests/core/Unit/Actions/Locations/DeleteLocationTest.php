<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Locations\DeleteLocation;
use Lunar\Core\Exceptions\LocationActionException;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a location without fulfilments or stock history', function () {
    $location = Location::factory()->create(['default' => false]);

    app(DeleteLocation::class)->execute($location);

    $this->assertDatabaseMissing('lunar_locations', ['id' => $location->id]);
});

test('refuses to delete the default location', function () {
    $location = Location::factory()->create(['default' => true]);

    expect(fn () => app(DeleteLocation::class)->execute($location))
        ->toThrow(LocationActionException::class);
});

test('refuses to delete a location with stock levels', function () {
    $location = Location::factory()->create(['default' => false]);
    StockLevel::factory()->create(['location_id' => $location->id]);

    expect(fn () => app(DeleteLocation::class)->execute($location))
        ->toThrow(LocationActionException::class);

    $this->assertDatabaseHas('lunar_locations', ['id' => $location->id]);
});

test('refuses to delete a location with stock movements', function () {
    $location = Location::factory()->create(['default' => false]);
    StockMovement::factory()->create(['location_id' => $location->id]);

    expect(fn () => app(DeleteLocation::class)->execute($location))
        ->toThrow(LocationActionException::class);
});

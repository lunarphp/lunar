<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Locations\UpdateLocation;
use Lunar\Core\Exceptions\LocationActionException;
use Lunar\Core\Models\Location;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the location attributes', function () {
    $location = Location::factory()->create(['name' => 'Old Name', 'default' => false]);

    app(UpdateLocation::class)->execute($location, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_locations', [
        'id' => $location->id,
        'name' => 'New Name',
    ]);
});

test('promoting to default demotes the previous default', function () {
    $previous = Location::factory()->create(['default' => true]);
    $location = Location::factory()->create(['default' => false]);

    app(UpdateLocation::class)->execute($location, ['default' => true]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($location->refresh()->default)->toBeTrue();
});

test('refuses to unset the default flag directly', function () {
    $location = Location::factory()->create(['default' => true]);

    expect(fn () => app(UpdateLocation::class)->execute($location, ['default' => false]))
        ->toThrow(LocationActionException::class);
});

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Locations\CreateLocation;
use Lunar\Core\Models\Location;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a location with the given attributes', function () {
    $location = app(CreateLocation::class)->execute([
        'name' => 'Leeds Warehouse',
        'handle' => 'leeds',
    ]);

    expect($location)->toBeInstanceOf(Location::class);

    $this->assertDatabaseHas('lunar_locations', [
        'id' => $location->id,
        'name' => 'Leeds Warehouse',
        'handle' => 'leeds',
    ]);
});

test('demotes the previous default when created as default', function () {
    $previous = Location::factory()->create(['default' => true]);

    $location = app(CreateLocation::class)->execute([
        'name' => 'Leeds Warehouse',
        'handle' => 'leeds',
        'default' => true,
    ]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($location->refresh()->default)->toBeTrue();
});

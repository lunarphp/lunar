<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\LocationResource;
use Lunar\Admin\Filament\Resources\LocationResource\Pages\CreateLocation;
use Lunar\Admin\Filament\Resources\LocationResource\Pages\EditLocation;
use Lunar\Core\Models\Location;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)->group('resource.location');

it('can render the locations list', function () {
    Location::factory()->default()->create();

    $this->asStaff(admin: true)
        ->get(LocationResource::getUrl('index'))
        ->assertSuccessful();
});

it('can create a location', function () {
    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreateLocation::class)
        ->fillForm([
            'name' => 'Main Warehouse',
            'handle' => 'main-warehouse',
            'default' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Location::class, [
        'name' => 'Main Warehouse',
        'handle' => 'main-warehouse',
        'default' => true,
    ]);
});

it('can edit a location', function () {
    $location = Location::factory()->create(['name' => 'Old name']);

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditLocation::class, ['record' => $location->getRouteKey()])
        ->fillForm(['name' => 'New name'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($location->refresh()->name)->toBe('New name');
});

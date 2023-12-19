<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.customer');

it('can render customer index page', function () {
    $this->asStaff(admin: true)
        ->get(CustomerResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list customers', function () {
    $this->asStaff();

    $customers = \Lunar\Models\Customer::factory(5)->create();

    Livewire::test(\Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers::class)
        ->assertCountTableRecords(5)
        ->assertCanSeeTableRecords($customers);
});

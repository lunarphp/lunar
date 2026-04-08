<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ListCustomers;
use Lunar\Models\Customer;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.customer');

it('can render customer index page', function () {
    $this->asStaff(admin: true)
        ->get(CustomerResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list customers', function () {
    $this->asStaff();

    $customers = Customer::factory(5)->create();

    Livewire::test(ListCustomers::class)
        ->assertCountTableRecords(5)
        ->assertCanSeeTableRecords($customers);
});

<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use Lunar\Models\Customer;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.customer');

it('can render customer view page', function () {
    $this->asStaff(admin: true)
        ->get(CustomerResource::getUrl('view', ['record' => Customer::factory()->create()]))
        ->assertSuccessful();
});

it('can delete a customer from the view page', function () {
    $customer = Customer::factory()->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(ViewCustomer::class, [
            'record' => $customer->getRouteKey(),
        ])
        ->callAction('delete');

    $this->assertModelMissing($customer);
});

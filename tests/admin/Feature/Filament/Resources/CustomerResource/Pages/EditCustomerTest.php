<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Models\Customer;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.customer');

it('can render customer edit page', function () {
    $this->asStaff(admin: true)
        ->get(CustomerResource::getUrl('edit', ['record' => Customer::factory()->create()]))
        ->assertSuccessful();
});

it('can retrieve customer data', function () {
    $this->asStaff();

    $customer = Customer::factory()->create();

    Livewire::test(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->assertFormSet([
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
        ]);
});

it('can save customer data', function () {
    $customer = Customer::factory()->create();
    $newData = Customer::factory()->make();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditCustomer::class, [
            'record' => $customer->getRouteKey(),
        ])
        ->fillForm([
            'first_name' => $newData->first_name,
            'last_name' => $newData->last_name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($customer->refresh())
        ->first_name->toBe($newData->first_name)
        ->last_name->toBe($newData->last_name);
});

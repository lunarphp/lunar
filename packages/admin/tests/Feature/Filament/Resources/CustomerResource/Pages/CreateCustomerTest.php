<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use Lunar\Models\Customer;

uses(\Lunar\Admin\Tests\Feature\Filament\TestCase::class)
    ->group('resource.customer');

it('can render customer create page', function () {
    $this->asStaff(admin: true)
        ->get(CustomerResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create customer', function () {
    $customer = Customer::factory()->make();

    $formData = [
        'title' => $customer->title,
        'first_name' => $customer->first_name,
        'last_name' => $customer->last_name,
        'company_name' => $customer->company_name,
    ];

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreateCustomer::class)
        ->fillForm($formData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Customer::class, $formData);
});

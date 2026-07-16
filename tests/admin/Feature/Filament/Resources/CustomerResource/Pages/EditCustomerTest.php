<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\CustomerResource;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\EditCustomer;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
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

it('can save attributes', function () {
    Language::factory()->create([
        'default' => true,
    ]);

    $record = Customer::factory()->create();

    $group = AttributeGroup::factory()->create([
        'name' => 'Details',
        'handle' => 'details',
        'position' => 1,
    ]);

    Attribute::factory()->modelType('customer')->create([
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => 'Name',
        'handle' => 'name',
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    $this->asStaff(admin: true);

    Livewire::test(EditCustomer::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'customerEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new Text('New Customer Name'),
        ],
    ])->call('save');

    expect($record->refresh()->attr('name'))->toBe('New Customer Name');
});

it('can delete a customer from the edit page', function () {
    $customer = Customer::factory()->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditCustomer::class, [
            'record' => $customer->getRouteKey(),
        ])
        ->callAction('delete');

    $this->assertModelMissing($customer);
});

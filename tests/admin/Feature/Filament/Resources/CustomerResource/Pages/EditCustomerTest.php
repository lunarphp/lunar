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

it('can save attributes', function () {
    \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $record = \Lunar\Models\Customer::factory()->create();

    $group = \Lunar\Models\AttributeGroup::factory()->create([
        'attributable_type' => 'customer',
        'name' => [
            'en' => 'Details',
        ],
        'handle' => 'details',
        'position' => 1,
    ]);

    $attribute = \Lunar\Models\Attribute::factory()->create([
        'attribute_type' => 'customer',
        'attribute_group_id' => $group->id,
        'position' => 1,
        'name' => [
            'en' => 'Name',
        ],
        'handle' => 'name',
        'section' => 'main',
        'required' => false,
        'system' => false,
        'searchable' => false,
    ]);

    \Illuminate\Support\Facades\DB::table('lunar_attributables')->insert([
        'attribute_id' => $attribute->id,
        'attributable_type' => 'customer',
        'attributable_id' => $record->id,
    ]);

    $this->asStaff(admin: true);

    \Livewire\Livewire::test(EditCustomer::class, [
        'record' => $record->getRouteKey(),
        'pageClass' => 'customerEdit',
    ])->fillForm([
        'attribute_data' => [
            'name' => new \Lunar\FieldTypes\Text('New Customer Name'),
        ],
    ])->call('save');

    expect($record->refresh()->attr('name'))->toBe('New Customer Name');
});

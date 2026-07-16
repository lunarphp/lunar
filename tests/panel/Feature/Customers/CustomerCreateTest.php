<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('renders the create form', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customers/Create')
            ->has('customerGroups')
            ->has('urls.store')
        );
});

it('creates a customer and redirects to its edit page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $response = $this->post(route('panel.customers.store'), [
        'title' => 'Mr',
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'company_name' => 'Analytical Engines Ltd',
        'tax_identifier' => 'TAX123',
        'account_ref' => 'ACC001',
    ]);

    $customer = Customer::sole();

    $response->assertRedirect(route('panel.customers.edit', $customer))
        ->assertSessionHas('success', 'Customer created.');

    expect($customer)
        ->title->toBe('Mr')
        ->first_name->toBe('Ada')
        ->last_name->toBe('Lovelace')
        ->company_name->toBe('Analytical Engines Ltd')
        ->tax_identifier->toBe('TAX123')
        ->account_ref->toBe('ACC001');
});

it('syncs the given customer groups on create', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $groups = CustomerGroup::factory()->count(2)->create();

    $this->post(route('panel.customers.store'), [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'customer_group_ids' => $groups->pluck('id')->all(),
    ]);

    $customer = Customer::sole();

    expect($customer->customerGroups->pluck('id')->sort()->values()->all())
        ->toBe($groups->pluck('id')->sort()->values()->all());
});

it('validates required fields on create', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->post(route('panel.customers.store'), [])
        ->assertSessionHasErrors(['first_name', 'last_name']);

    expect(Customer::count())->toBe(0);
});

it('rejects unknown customer group ids on create', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->post(route('panel.customers.store'), [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'customer_group_ids' => [999999],
    ])->assertSessionHasErrors(['customer_group_ids.0']);
});

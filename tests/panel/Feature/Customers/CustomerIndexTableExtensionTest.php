<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\Fixtures\CustomerFixtureTestCase;

uses(CustomerFixtureTestCase::class);

it('merges add-on table extension columns onto the customer index', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    Address::factory()->count(2)->for($customer)->create();

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('columns', function ($columns) {
                $keys = collect($columns)->pluck('key')->all();

                // First-party columns still come first, extension columns are appended.
                return $keys === ['full_name', 'company_name', 'customer_groups', 'created_at', 'public_id', 'addresses_count'];
            })
            ->where('customers.data.0.public_id', $customer->public_id)
            // "addresses_count" only appears with a real value because the extension
            // column's query() hook (withCount) was actually applied before pagination.
            ->where('customers.data.0.addresses_count', 2)
        );
});

it('shares add-on filter definitions and their current values', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.index', ['filter' => ['has_company' => 'yes']]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('tableFilters.0.key', 'has_company')
            ->where('tableFilters.0.options', ['yes' => 'Has company', 'no' => 'No company'])
            ->where('tableFilterValues.has_company', 'yes')
        );
});

it('applies an add-on filter to the customer index query', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $business = Customer::factory()->create(['company_name' => 'Acme Ltd']);
    Customer::factory()->create(['company_name' => null]);

    $this->get(route('panel.customers.index', ['filter' => ['has_company' => 'yes']]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('customers.data', 1)
            ->where('customers.data.0.id', $business->id)
        );
});

it('ignores empty add-on filter values', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Customer::factory()->create(['company_name' => 'Acme Ltd']);
    Customer::factory()->create(['company_name' => null]);

    $this->get(route('panel.customers.index', ['filter' => ['has_company' => '']]))
        ->assertInertia(fn (Assert $page) => $page->has('customers.data', 2));
});

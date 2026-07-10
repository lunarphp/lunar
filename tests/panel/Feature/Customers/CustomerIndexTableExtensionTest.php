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

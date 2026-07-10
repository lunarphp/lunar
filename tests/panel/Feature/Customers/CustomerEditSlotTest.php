<?php

use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\Fixtures\CustomerFixtureTestCase;

uses(CustomerFixtureTestCase::class);

it('shares an add-on slot registered on the customer edit zone', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.edit', $customer))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            // The zone key contains dots ("customers.edit:main:after"), so it can't be
            // reached via dot-notation `where`/`has` path assertions — inspect directly.
            ->where('slots', function ($slots) {
                $zone = $slots->get('customers.edit:main:after');

                return $zone !== null
                    && count($zone) === 1
                    && $zone[0]['component'] === 'customer-fixture::Banner'
                    && $zone[0]['props']['message'] === 'Injected by the fixture add-on.';
            })
        );
});

it('does not share the customer edit slot on unrelated pages', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.index'))
        ->assertInertia(fn ($page) => $page
            ->where('slots', fn ($slots) => $slots->get('customers.edit:main:after') === null)
        );
});

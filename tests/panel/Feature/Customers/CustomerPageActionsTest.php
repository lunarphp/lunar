<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\Fixtures\CustomerFixtureTestCase;

uses(CustomerFixtureTestCase::class);

it('shares add-on page actions with a per-record url on a record page', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.edit', $customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('pageActions', fn ($actions) => collect($actions)->pluck('key')->all() === ['impersonate'])
            ->where('pageActions.0.url', route('panel.customers.edit', $customer))
        );
});

it('shares no page actions on a page with none registered', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('pageActions', []));
});

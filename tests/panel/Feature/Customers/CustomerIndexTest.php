<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

it('redirects guests away from the customer index', function () {
    $this->get(route('panel.customers.index'))->assertRedirect(route('panel.login'));
});

it('renders the customer index for authenticated staff', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Customer::factory()->count(3)->create();

    $this->get(route('panel.customers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customers/Index')
            ->has('customers.data', 3)
            ->has('columns')
            ->has('customerGroups')
            ->has('urls.create')
        );
});

it('searches customers by name, company, tax identifier and account ref', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $match = Customer::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    Customer::factory()->create(['first_name' => 'Grace', 'last_name' => 'Hopper']);

    $this->get(route('panel.customers.index', ['q' => 'Ada']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('customers.data', 1)
            ->where('customers.data.0.id', $match->id)
        );
});

it('filters customers by customer group', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $group = CustomerGroup::factory()->create();
    $inGroup = Customer::factory()->create();
    $inGroup->customerGroups()->sync([$group->id]);
    Customer::factory()->create();

    $this->get(route('panel.customers.index', ['customer_group_id' => $group->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('customers.data', 1)
            ->where('customers.data.0.id', $inGroup->id)
        );
});

it('sorts customers by an allowed column and direction', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $a = Customer::factory()->create(['first_name' => 'Aaron']);
    $z = Customer::factory()->create(['first_name' => 'Zoe']);

    $this->get(route('panel.customers.index', ['sort' => 'first_name', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('customers.data.0.id', $a->id)
            ->where('customers.data.1.id', $z->id)
        );
});

it('falls back to created_at when given a non-sortable column', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Customer::factory()->create();

    $this->get(route('panel.customers.index', ['sort' => 'meta']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('filters.sort', 'meta'));
});

it('searches customers by linked user email', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $match = Customer::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    $match->users()->attach(User::factory()->create(['email' => 'ada@example.com']));
    Customer::factory()->create(['first_name' => 'Grace', 'last_name' => 'Hopper']);

    $this->get(route('panel.customers.index', ['q' => 'ada@example.com']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('customers.data', 1)
            ->where('customers.data.0.id', $match->id)
        );
});

it('exposes real KPI counts', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Customer::factory()->create(['company_name' => 'Acme Ltd']);
    Customer::factory()->create(['company_name' => null, 'created_at' => now()->subDays(40)]);

    Customer::factory()->create(['company_name' => null]);

    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('kpis.total', 3)
            ->where('kpis.newLast30Days', 2)
            ->where('kpis.business', 1)
            ->where('kpis.avgLifetimeValue', null)
            ->where('kpis.avgLifetimeValueDelta', null)
        );
});

it('reports the average lifetime value across customers with orders', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $first = Customer::factory()->create();
    Order::factory()->placed()->for($first)->create(['total' => 10000, 'exchange_rate' => 1]);
    Order::factory()->placed()->for($first)->create(['total' => 5000, 'exchange_rate' => 1]);

    $second = Customer::factory()->create();
    Order::factory()->placed()->for($second)->create(['total' => 5000, 'exchange_rate' => 1]);

    // (150 + 50) / 2 customers = £100.00; no orders predate the 30-day window, so no delta.
    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('kpis.avgLifetimeValue', '£100.00')
            ->where('kpis.avgLifetimeValueDelta', null)
        );
});

it('reports the lifetime value delta against the average 30 days ago', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $customer = Customer::factory()->create();
    Order::factory()->placed()->for($customer)->create(['total' => 10000, 'exchange_rate' => 1, 'placed_at' => now()->subDays(40)]);
    Order::factory()->placed()->for($customer)->create(['total' => 2000, 'exchange_rate' => 1]);

    // Average moved from £100.00 to £120.00: +20%.
    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('kpis.avgLifetimeValue', '£120.00')
            ->where('kpis.avgLifetimeValueDelta', 20)
        );
});

it('exposes per-row order stats and the linked user email', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $customer = Customer::factory()->create();
    $customer->users()->attach(User::factory()->create(['email' => 'ada@example.com']));

    Order::factory()->placed()->for($customer)->create(['total' => 10000, 'exchange_rate' => 1]);
    Order::factory()->placed()->for($customer)->create(['total' => 5000, 'exchange_rate' => 1]);
    Order::factory()->for($customer)->create(['total' => 99999, 'placed_at' => null]);

    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('customers.data.0.email', 'ada@example.com')
            ->where('customers.data.0.orders_count', 2)
            ->where('customers.data.0.total_spend', '£150.00')
            ->whereNot('customers.data.0.last_order_at', null)
        );
});

it('reports empty row stats for customers without placed orders', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Customer::factory()->create();

    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('customers.data.0.email', null)
            ->where('customers.data.0.orders_count', 0)
            ->where('customers.data.0.total_spend', null)
            ->where('customers.data.0.last_order_at', null)
        );
});

it('paginates the customer index', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Customer::factory()->count(20)->create();

    $this->get(route('panel.customers.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('customers.data', 15)
            ->where('customers.total', 20)
            ->where('customers.last_page', 2)
        );

    $this->get(route('panel.customers.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('customers.data', 5));
});

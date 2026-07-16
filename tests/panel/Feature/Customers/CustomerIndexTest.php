<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Staff;
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

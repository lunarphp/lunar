<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Panel\TestCase;
use Spatie\Activitylog\Models\Activity;

uses(TestCase::class);

it('renders the edit page with addresses, users and activity', function () {
    $staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($staff, 'staff');

    $customer = Customer::factory()->create();
    $address = Address::factory()->for($customer)->create();
    $user = User::factory()->create();
    $customer->users()->attach($user);

    Activity::create([
        'description' => 'created',
        'subject_type' => $customer->getMorphClass(),
        'subject_id' => $customer->id,
        'causer_type' => $staff->getMorphClass(),
        'causer_id' => $staff->id,
        'log_name' => 'default',
    ]);

    $this->get(route('panel.customers.edit', $customer))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('customers/Edit')
            ->where('customer.id', $customer->id)
            ->has('addresses', 1)
            ->where('addresses.0.id', $address->id)
            ->has('users', 1)
            ->where('users.0.id', $user->id)
            ->has('activities', 1)
        );
});

it('exposes lifetime stats and order history for placed orders', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $customer = Customer::factory()->create();

    Order::factory()->placed()->for($customer)->create(['total' => 10000, 'exchange_rate' => 1]);
    Order::factory()->placed()->for($customer)->create(['total' => 5000, 'exchange_rate' => 1]);
    Order::factory()->for($customer)->create(['total' => 99999, 'placed_at' => null]);

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.orders', 2)
            ->where('stats.totalSpend', '£150.00')
            ->where('stats.avgOrder', '£75.00')
            ->whereNot('stats.latestOrderAt', null)
            ->has('orders', 2)
            ->has('orders.0', fn (Assert $order) => $order
                ->hasAll(['id', 'reference', 'status', 'status_label', 'placed_at', 'total'])
            )
        );
});

it('reports empty lifetime stats when the customer has no placed orders', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.orders', 0)
            ->where('stats.totalSpend', null)
            ->where('stats.avgOrder', null)
            ->where('stats.latestOrderAt', null)
            ->has('orders', 0)
        );
});

it('buckets placed order value into the order chart', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $customer = Customer::factory()->create();

    Order::factory()->placed()->for($customer)->create(['total' => 10000, 'exchange_rate' => 1, 'placed_at' => now()->subMonth()]);
    Order::factory()->placed()->for($customer)->create(['total' => 5000, 'exchange_rate' => 1, 'placed_at' => now()]);
    Order::factory()->for($customer)->create(['total' => 99999, 'placed_at' => null]);

    $this->get(route('panel.customers.edit', $customer))
        ->assertInertia(fn (Assert $page) => $page
            ->component('customers/Edit')
            ->loadDeferredProps(fn (Assert $chart) => $chart
                ->where('orderChart.range', '12m')
                ->has('orderChart.buckets', 12)
                ->where('orderChart.buckets.10.value', 100)
                ->where('orderChart.buckets.10.display', '£100.00')
                ->where('orderChart.buckets.11.value', 50)
                ->where('orderChart.buckets.0.value', 0)
            )
        );
});

it('zooms the order chart out to yearly buckets', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Currency::factory()->create(['code' => 'GBP', 'default' => true, 'exchange_rate' => 1]);

    $customer = Customer::factory()->create();

    Order::factory()->placed()->for($customer)->create(['total' => 20000, 'exchange_rate' => 1, 'placed_at' => now()->subYears(2)]);

    $this->get(route('panel.customers.edit', ['customer' => $customer, 'chart_range' => '10y']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('customers/Edit')
            ->loadDeferredProps(fn (Assert $chart) => $chart
                ->where('orderChart.range', '10y')
                ->has('orderChart.buckets', 10)
                ->where('orderChart.buckets.7.value', 200)
                ->where('orderChart.buckets.9.value', 0)
            )
        );
});

it('falls back to the default chart range for unknown values', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->get(route('panel.customers.edit', ['customer' => $customer, 'chart_range' => 'banana']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('customers/Edit')
            ->loadDeferredProps(fn (Assert $chart) => $chart
                ->where('orderChart.range', '12m')
                ->has('orderChart.buckets', 12)
            )
        );
});

it('updates a customer and syncs its customer groups', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $originalGroup = CustomerGroup::factory()->create();
    $customer->customerGroups()->sync([$originalGroup->id]);

    $newGroup = CustomerGroup::factory()->create();

    $this->put(route('panel.customers.update', $customer), [
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'customer_group_ids' => [$newGroup->id],
    ])->assertRedirect()
        ->assertSessionHas('success', 'Customer updated.');

    $customer->refresh();

    expect($customer->first_name)->toBe('Updated')
        ->and($customer->last_name)->toBe('Name')
        ->and($customer->customerGroups->pluck('id')->all())->toBe([$newGroup->id]);
});

it('clears customer groups when an empty array is given on update', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();
    $customer->customerGroups()->sync([$group->id]);

    $this->put(route('panel.customers.update', $customer), [
        'first_name' => $customer->first_name,
        'last_name' => $customer->last_name,
    ]);

    expect($customer->customerGroups()->count())->toBe(0);
});

it('validates required fields on update', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->put(route('panel.customers.update', $customer), [])
        ->assertSessionHasErrors(['first_name', 'last_name']);
});

it('deletes a customer', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $customer = Customer::factory()->create();

    $this->delete(route('panel.customers.destroy', $customer))
        ->assertRedirect(route('panel.customers.index'))
        ->assertSessionHas('success', 'Customer deleted.');

    expect(Customer::find($customer->id))->toBeNull();
});

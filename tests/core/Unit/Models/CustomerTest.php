<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Order;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can make a customer with minimum attributes', function () {
    $customer = [
        'title' => null,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'company_name' => null,
        'tax_identifier' => null,
        'meta' => null,
    ];

    Customer::create($customer);

    $customer['meta'] = json_encode($customer['meta']);

    $this->assertDatabaseHas(
        'lunar_customers',
        $customer
    );
});

test('can make a customer', function () {
    $customer = [
        'title' => 'Mr.',
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'company_name' => 'Stark Enterprises',
        'tax_identifier' => null,
        'meta' => null,
    ];

    Customer::create($customer);

    $customer['meta'] = json_encode($customer['meta']);

    $this->assertDatabaseHas(
        'lunar_customers',
        $customer
    );
});

test('can make a customer with meta attribute', function () {
    $customer = [
        'title' => null,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'company_name' => null,
        'tax_identifier' => null,
        'meta' => [
            'account' => 123456,
        ],
    ];

    $customer = Customer::create($customer);

    expect($customer->meta['account'])->toEqual(123456);
});

test('can get full name', function () {
    $customer = Customer::factory()->create([
        'title' => null,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
    ]);

    expect($customer->fullName)->toEqual("$customer->first_name $customer->last_name");

    $customer = Customer::factory()->create([
        'title' => 'Mr.',
        'first_name' => 'Tony',
        'last_name' => 'Stark',
    ]);

    expect($customer->fullName)->toEqual("$customer->title $customer->first_name $customer->last_name");

    $customer = Customer::factory()->create([
        'title' => 'Mr.',
        'first_name' => '',
        'last_name' => 'Stark',
    ]);

    expect($customer->fullName)->toEqual("$customer->title $customer->last_name");

    $customer = Customer::factory()->create([
        'title' => 'Mr.',
        'first_name' => 'Tony',
        'last_name' => '',
    ]);

    expect($customer->fullName)->toEqual("$customer->title $customer->first_name");
});

test('can associate to customer groups', function () {
    $groups = CustomerGroup::factory(4)->create();
    $customer = Customer::factory()->create();

    $customer->customerGroups()->sync($groups->pluck('id'));

    expect($customer->customerGroups)->toHaveCount($groups->count());
});

test('can associate to users', function () {
    $users = User::factory(4)->create();
    $customer = Customer::factory()->create();

    $customer->users()->sync($users->pluck('id'));

    expect($customer->users)->toHaveCount($users->count());
});

test('can fetch customer addresses', function () {
    $customer = Customer::factory()->create();
    $addresses = Address::factory(2)->create([
        'customer_id' => $customer->id,
    ]);

    expect($customer->addresses()->get())->toHaveCount($addresses->count());
});

test('can delete a customer without foreign key violations', function () {
    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();
    $discount = Discount::factory()->create();
    $user = User::factory()->create();

    Cart::factory()->create(['customer_id' => $customer->id]);
    Order::factory()->create(['customer_id' => $customer->id]);
    Address::factory()->create(['customer_id' => $customer->id]);

    $customer->customerGroups()->attach($group);
    $customer->discounts()->attach($discount);
    $customer->users()->attach($user);

    $customer->delete();

    $this->assertDatabaseMissing('lunar_customers', ['id' => $customer->id]);
});

test('deleting a customer preserves orders and carts with customer_id nulled', function () {
    $customer = Customer::factory()->create();
    $cart = Cart::factory()->create(['customer_id' => $customer->id]);
    $order = Order::factory()->create(['customer_id' => $customer->id]);

    $customer->delete();

    $this->assertDatabaseHas('lunar_carts', ['id' => $cart->id, 'customer_id' => null]);
    $this->assertDatabaseHas('lunar_orders', ['id' => $order->id, 'customer_id' => null]);
});

test('deleting a customer removes owned addresses and pivot rows', function () {
    $customer = Customer::factory()->create();
    $address = Address::factory()->create(['customer_id' => $customer->id]);
    $group = CustomerGroup::factory()->create();
    $user = User::factory()->create();

    $customer->customerGroups()->attach($group);
    $customer->users()->attach($user);

    $customer->delete();

    $this->assertDatabaseMissing('lunar_addresses', ['id' => $address->id]);
    $this->assertDatabaseMissing('lunar_customer_customer_group', ['customer_id' => $customer->id]);
    $this->assertDatabaseMissing('lunar_customer_user', ['customer_id' => $customer->id]);
});

test('can retrieve latest customer', function () {
    Config::set('auth.providers.users.model', 'Lunar\Tests\Core\Stubs\User');

    $user = User::factory()->create();

    $customers = Customer::factory(5)->create();

    $user->customers()->sync($customers->pluck('id'));

    expect($user->customers()->get())->toHaveCount(5);

    $this->assertDatabaseCount((new Customer)->getTable(), 5);

    expect($user->latestCustomer()->id)->toEqual($customers->last()->id);
});

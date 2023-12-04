<?php

uses(\Lunar\Tests\TestCase::class);
use Illuminate\Support\Facades\Config;
use Lunar\Models\Address;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Stubs\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a customer with minimum attributes', function () {
    $customer = [
        'title' => null,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'company_name' => null,
        'vat_no' => null,
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
        'vat_no' => null,
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
        'vat_no' => null,
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

test('can retrieve latest customer', function () {
    Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');

    $user = User::factory()->create();

    $customers = Customer::factory(5)->create();

    $user->customers()->sync($customers->pluck('id'));

    expect($user->customers()->get())->toHaveCount(5);

    $this->assertDatabaseCount((new Customer)->getTable(), 5);

    expect($user->latestCustomer()->id)->toEqual($customers->last()->id);
});

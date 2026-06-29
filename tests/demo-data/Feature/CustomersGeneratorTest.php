<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Customer;
use Lunar\DemoData\Generators\CustomersGenerator;
use Lunar\DemoData\Generators\FoundationGenerator;
use Lunar\DemoData\Support\DemoContext;
use Lunar\Tests\Admin\Stubs\User;
use Lunar\Tests\DemoData\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function generateCustomers(): DemoContext
{
    $context = DemoContext::fromConfig('small');

    app(FoundationGenerator::class)->generate($context);
    app(CustomersGenerator::class)->generate($context);

    return $context;
}

test('it creates customers in the default group with a default address', function () {
    generateCustomers();

    expect(Customer::count())->toBe(8);

    $customer = Customer::query()->first();

    expect($customer->customerGroups()->where('handle', 'retail')->exists())->toBeTrue();
    expect($customer->addresses()->count())->toBe(1);

    $address = $customer->addresses()->first();
    expect($address->billing_default)->toBeTrue();
    expect($address->shipping_default)->toBeTrue();
});

test('it links roughly half of the customers to a user account', function () {
    generateCustomers();

    // Even indices (0,2,4,6) of the eight customers are linked.
    expect(User::count())->toBe(4);
    expect(Customer::query()->has('users')->count())->toBe(4);
});

test('it is idempotent', function () {
    $context = generateCustomers();
    app(CustomersGenerator::class)->generate($context);

    expect(Customer::count())->toBe(8);
    expect(User::count())->toBe(4);
});

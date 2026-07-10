<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\CreateCustomerAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates an address on the customer', function () {
    $customer = Customer::factory()->create();
    $country = Country::factory()->create();

    $address = app(CreateCustomerAddress::class)->execute($customer, [
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'line_one' => '10880 Malibu Point',
        'city' => 'Malibu',
        'country_id' => $country->id,
    ]);

    expect($address)->toBeInstanceOf(Address::class);
    expect($address->customer_id)->toBe($customer->id);

    $this->assertDatabaseHas('lunar_addresses', [
        'id' => $address->id,
        'customer_id' => $customer->id,
        'line_one' => '10880 Malibu Point',
        'city' => 'Malibu',
    ]);
});

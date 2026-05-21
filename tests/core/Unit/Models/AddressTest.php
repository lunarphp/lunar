<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Customer;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('models', 'cross-db');

use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

test('can make an address with minimal attributes', function () {
    $country = Country::factory()->create();
    $customer = Customer::factory()->create();

    $data = [
        'country_id' => $country->id,
        'customer_id' => $customer->id,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'line_one' => 'Stark Industries Headquarters',
        'city' => 'Los Angeles',
        'shipping_default' => true,
    ];

    Address::create($data);

    $this->assertDatabaseHas('lunar_addresses', $data);
});

test('can make a full address', function () {
    $country = Country::factory()->create();
    $customer = Customer::factory()->create();

    $data = [
        'country_id' => $country->id,
        'customer_id' => $customer->id,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'line_one' => 'Stark Industries Headquarters',
        'line_two' => 'Line Two',
        'line_three' => 'Line Three',
        'state' => 'Southern California',
        'postcode' => 123456,
        'delivery_instructions' => 'Pass on to Happy',
        'contact_email' => 'deliveries@stark.com',
        'contact_phone' => '123123123',
        'meta' => [
            'door_code' => 0000,
        ],
        'shipping_default' => true,
        'billing_default' => true,
        'city' => 'Los Angeles',
    ];

    $address = Address::create($data);

    $expectedMeta = $data['meta'];
    unset($data['meta']);

    $this->assertDatabaseHas('lunar_addresses', $data);

    expect((array) $address->fresh()->meta)->toEqual($expectedMeta);
    expect($address->customer)->toBeInstanceOf(Customer::class);
    expect($address->country)->toBeInstanceOf(Country::class);
});

test('can delete address', function () {
    $country = Country::factory()->create();
    $customer = Customer::factory()->create();

    $data = [
        'country_id' => $country->id,
        'customer_id' => $customer->id,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'line_one' => 'Stark Industries Headquarters',
        'line_two' => 'Line Two',
        'line_three' => 'Line Three',
        'state' => 'Southern California',
        'postcode' => 123456,
        'delivery_instructions' => 'Pass on to Happy',
        'contact_email' => 'deliveries@stark.com',
        'contact_phone' => '123123123',
        'meta' => [
            'door_code' => 0000,
        ],
        'shipping_default' => true,
        'billing_default' => true,
        'city' => 'Los Angeles',
    ];

    $address = Address::create($data);

    $address->delete();

    assertDatabaseMissing(Address::class, [
        'id' => $address->id,
    ]);
});

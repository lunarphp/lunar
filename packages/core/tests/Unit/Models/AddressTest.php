<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Models\Address;
use GetCandy\Models\Country;
use GetCandy\Models\Customer;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group models
 */
class AddressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_an_address_with_minimal_attributes()
    {
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

        $this->assertDatabaseHas('getcandy_addresses', $data);
    }

    /** @test */
    public function can_make_a_full_address()
    {
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

        $data['meta'] = json_encode($data['meta']);

        $this->assertDatabaseHas('getcandy_addresses', $data);

        $this->assertInstanceOf(Customer::class, $address->customer);
        $this->assertInstanceOf(Country::class, $address->country);
    }
}

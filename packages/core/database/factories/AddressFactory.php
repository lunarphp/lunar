<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Address;
use GetCandy\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'country_id'            => Country::factory(),
            'title'                 => $this->faker->title,
            'first_name'            => $this->faker->firstName,
            'last_name'             => $this->faker->lastName,
            'company_name'          => $this->faker->boolean ? $this->faker->company : null,
            'line_one'              => $this->faker->streetName,
            'line_two'              => $this->faker->boolean ? $this->faker->secondaryAddress : null,
            'line_three'            => $this->faker->boolean ? $this->faker->buildingNumber : null,
            'city'                  => $this->faker->city,
            'state'                 => $this->faker->boolean ? $this->faker->state : null,
            'postcode'              => $this->faker->boolean ? $this->faker->postcode : null,
            'delivery_instructions' => $this->faker->boolean ? $this->faker->sentence : null,
            'contact_email'         => $this->faker->boolean ? $this->faker->safeEmail : null,
            'contact_phone'         => $this->faker->boolean ? $this->faker->phoneNumber : null,
            'meta'                  => $this->faker->boolean ? ['has_dog' => 'yes'] : null,
            'shipping_default'      => $this->faker->boolean,
            'billing_default'       => $this->faker->boolean,
        ];
    }
}

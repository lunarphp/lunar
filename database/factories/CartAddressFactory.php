<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartAddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->title,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'company_name' => $this->faker->boolean ? $this->faker->company : null,
            'line_one' => $this->faker->streetName,
            'line_two' => $this->faker->boolean ? $this->faker->secondaryAddress : null,
            'line_three' => $this->faker->boolean ? $this->faker->buildingNumber : null,
            'city' => $this->faker->city,
            'state' => $this->faker->boolean ? $this->faker->state : null,
            'postcode' => $this->faker->postcode,
            'delivery_instructions' => $this->faker->boolean ? $this->faker->sentence : null,
            'contact_email' => $this->faker->boolean ? $this->faker->safeEmail : null,
            'contact_phone' => $this->faker->boolean ? $this->faker->phoneNumber : null,
            'type' => 'shipping',
            'meta' => $this->faker->boolean ? ['has_dog' => 'yes'] : null,
        ];
    }
}

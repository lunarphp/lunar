<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->title,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'company_name' => $this->faker->boolean ? $this->faker->company : null,
            'vat_no' => $this->faker->boolean ? Str::random() : null,
            'meta' => $this->faker->boolean ? ['account_no' => Str::random()] : null,
        ];
    }
}

<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Customer;

class CustomerFactory extends BaseFactory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'title' => $this->faker->title,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'company_name' => $this->faker->boolean ? $this->faker->company : null,
            'tax_identifier' => $this->faker->boolean ? Str::random() : null,
            'meta' => $this->faker->boolean ? ['account_no' => Str::random()] : null,
        ];
    }
}

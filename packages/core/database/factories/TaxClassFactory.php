<?php

namespace Lunar\Database\Factories;

use Lunar\Models\TaxClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxClassFactory extends Factory
{
    protected $model = TaxClass::class;

    public function definition(): array
    {
        return [
            'name'    => $this->faker->name,
            'default' => false,
        ];
    }
}

<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Models\State;

class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->country,
            'code' => Str::random(),
        ];
    }
}

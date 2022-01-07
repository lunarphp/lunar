<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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

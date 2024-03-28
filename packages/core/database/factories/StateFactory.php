<?php

namespace Lunar\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Models\State;

class StateFactory extends BaseFactory
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

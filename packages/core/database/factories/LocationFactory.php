<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Location;

class LocationFactory extends BaseFactory
{
    protected $model = Location::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'handle' => Str::slug($name).'-'.Str::random(5),
            'default' => false,
            'meta' => null,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['default' => true]);
    }
}

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
            'public_id' => (string) Str::ulid(),
            'name' => $name,
            'handle' => Str::slug($name).'-'.Str::random(5),
            'default' => false,
            'accepts_pickup' => false,
            'meta' => null,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['default' => true]);
    }

    public function acceptsPickup(): static
    {
        return $this->state(fn () => ['accepts_pickup' => true]);
    }
}

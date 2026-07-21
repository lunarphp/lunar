<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\ProductType;
use Lunar\Core\States\ProductType\Active;
use Lunar\Core\States\ProductType\Draft;

class ProductTypeFactory extends BaseFactory
{
    protected $model = ProductType::class;

    public function definition(): array
    {
        $name = $this->faker->name();

        // Explicit handle: the model's creating hook is swallowed under
        // Event::fake(), and distinct names can slug identically — a unique
        // suffix keeps factory product types collision-free.
        return [
            'public_id' => (string) Str::ulid(),
            'name' => $name,
            'handle' => Str::slug($name.' '.$this->faker->unique()->numberBetween(1, 9999999)),
            'status' => Active::$name,
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => Active::$name]);
    }

    public function draft(): self
    {
        return $this->state(['status' => Draft::$name]);
    }
}

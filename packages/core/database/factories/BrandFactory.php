<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Brand;
use Lunar\Core\States\Brand\Active;
use Lunar\Core\States\Brand\Draft;

class BrandFactory extends BaseFactory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        // Explicit handle: the model's creating hook is swallowed under
        // Event::fake(), and distinct company names can slug identically —
        // a unique suffix keeps factory brands collision-free.
        return [
            'public_id' => (string) Str::ulid(),
            'name' => $name,
            'handle' => Str::slug($name.' '.$this->faker->unique()->numberBetween(1, 9999999)),
            'status' => Active::$name,
            'description' => collect(['en' => $this->faker->paragraph]),
            'short_description' => collect(['en' => $this->faker->sentence]),
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

<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Brand;

class BrandFactory extends BaseFactory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'name' => $this->faker->company(),
            'description' => collect(['en' => $this->faker->paragraph]),
            'short_description' => collect(['en' => $this->faker->sentence]),
        ];
    }
}

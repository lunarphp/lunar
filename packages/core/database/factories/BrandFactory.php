<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Brand;

class BrandFactory extends BaseFactory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'description' => collect(['en' => $this->faker->paragraph]),
            'short_description' => collect(['en' => $this->faker->sentence]),
        ];
    }
}

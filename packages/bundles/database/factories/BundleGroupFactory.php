<?php

namespace Lunar\Bundles\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleGroup;

class BundleGroupFactory extends Factory
{
    protected $model = BundleGroup::class;

    public function definition(): array
    {
        return [
            'bundle_id' => Bundle::factory(),
            'name' => ['en' => $this->faker->words(2, true)],
            'min_selections' => 1,
            'max_selections' => 1,
            'position' => 0,
        ];
    }
}

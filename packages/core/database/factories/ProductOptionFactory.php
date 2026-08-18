<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\ProductOption;

class ProductOptionFactory extends BaseFactory
{
    private static $position = 1;

    protected $model = ProductOption::class;

    public function definition(): array
    {
        $name = $this->faker->name;

        return [
            'public_id' => (string) Str::ulid(),
            'handle' => Str::slug($name),
            'name' => [
                'en' => $name,
            ],
            'label' => [
                'en' => $name,
            ],
            'type' => 'text',
            'shared' => true,
        ];
    }

    public function colour(): static
    {
        return $this->state(['type' => 'colour']);
    }

    public function swatch(): static
    {
        return $this->state(['type' => 'swatch']);
    }
}

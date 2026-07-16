<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\ProductOptionValue;

class ProductOptionValueFactory extends BaseFactory
{
    protected $model = ProductOptionValue::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'name' => [
                'en' => $this->faker->name,
            ],
        ];
    }
}

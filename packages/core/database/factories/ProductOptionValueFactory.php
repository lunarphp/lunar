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

    public function colour(?string $hex = null): static
    {
        return $this->state(fn () => [
            'meta' => ['colour' => strtoupper($hex ?? $this->faker->hexColor())],
        ]);
    }
}

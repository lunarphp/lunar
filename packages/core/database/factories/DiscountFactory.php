<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Discount;

class DiscountFactory extends BaseFactory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->name;

        return [
            'public_id' => (string) Str::ulid(),
            'name' => $name,
            'handle' => Str::snake($name),
            'type' => PercentageOff::class,
            'starts_at' => now(),
        ];
    }
}

<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\CartLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\StockReservation;

class StockReservationFactory extends Factory
{
    protected $model = StockReservation::class;

    public function definition(): array
    {
        return [
            'stockable_id' => CartLine::factory(),
            'stockable_type' => CartLine::class,
            'variant_id' => ProductVariant::factory(),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'expires_at' => now(),
        ];
    }
}

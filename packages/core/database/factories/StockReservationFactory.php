<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\ProductVariant;
use Lunar\Models\StockReservation;

class StockReservationFactory extends Factory
{
    protected $model = StockReservation::class;

    public function definition(): array
    {
        return [
            'stockable_id' => ProductVariant::factory(),
            'stockable_type' => ProductVariant::class,
            'quantity' => $this->faker->numberBetween(0, 1000),
            'expires_at' => now(),
        ];
    }
}

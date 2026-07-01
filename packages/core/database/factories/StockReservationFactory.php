<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockReservation;

class StockReservationFactory extends BaseFactory
{
    protected $model = StockReservation::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}

<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockMovement;

class StockMovementFactory extends BaseFactory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'product_variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'type' => StockMovementType::Received,
        ];
    }
}

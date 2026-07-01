<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;

class StockLevelFactory extends BaseFactory
{
    protected $model = StockLevel::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'product_variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'on_hand' => 0,
            'incoming' => 0,
            'committed' => 0,
            'unavailable' => 0,
        ];
    }
}

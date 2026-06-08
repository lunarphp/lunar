<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentTracking;

class FulfilmentTrackingFactory extends BaseFactory
{
    protected $model = FulfilmentTracking::class;

    public function definition(): array
    {
        return [
            'fulfilment_id' => Fulfilment::factory(),
            'tracking_number' => $this->faker->unique()->regexify('[A-Z0-9]{12}'),
            'tracking_url' => $this->faker->url(),
            'shipping_method' => $this->faker->randomElement(['Standard', 'Express', 'Next Day']),
            'meta' => null,
        ];
    }
}

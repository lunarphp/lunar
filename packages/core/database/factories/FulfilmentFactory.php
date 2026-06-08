<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;

class FulfilmentFactory extends BaseFactory
{
    protected $model = Fulfilment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'location_id' => Location::factory(),
            'reference' => $this->faker->unique()->regexify('[A-Z]{10}'),
            'state' => 'pending',
            'notes' => null,
            'meta' => null,
            'shipped_at' => null,
        ];
    }

    public function shipped(): static
    {
        return $this->state(fn () => [
            'state' => 'shipped',
            'shipped_at' => now(),
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn () => [
            'state' => 'returned',
            'shipped_at' => now(),
        ]);
    }
}

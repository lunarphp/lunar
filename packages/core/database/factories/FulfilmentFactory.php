<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Order;

class FulfilmentFactory extends BaseFactory
{
    protected $model = Fulfilment::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'order_id' => Order::factory(),
            'location_id' => Location::factory(),
            'reference' => $this->faker->unique()->regexify('[A-Z]{10}'),
            'method' => 'shipping',
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

    public function pickup(): static
    {
        return $this->state(fn () => ['method' => 'pickup']);
    }

    public function pickedUp(): static
    {
        return $this->state(fn () => [
            'method' => 'pickup',
            'state' => 'picked-up',
            'shipped_at' => now(),
        ]);
    }

    public function digital(): static
    {
        return $this->state(fn () => ['method' => 'digital']);
    }

    public function provisioned(): static
    {
        return $this->state(fn () => [
            'method' => 'digital',
            'state' => 'provisioned',
            'shipped_at' => now(),
        ]);
    }
}

<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

class TransactionFactory extends BaseFactory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'order_id' => Order::factory(),
            'success' => true,
            'type' => $this->faker->boolean(85) ? 'capture' : 'refund',
            'driver' => 'lunar',
            'amount' => 100,
            'reference' => $this->faker->unique()->regexify('[A-Z]{8}'),
            'status' => 'settled',
            'notes' => null,
            'card_type' => $this->faker->creditCardType,
            'last_four' => $this->faker->numberBetween(1000, 9999),
            'meta' => null,
        ];
    }
}

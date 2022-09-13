<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'order_id'  => Order::factory(),
            'success'   => true,
            'type'      => $this->faker->boolean(85) ? 'capture' : 'refund',
            'driver'    => 'getcandy',
            'amount'    => 100,
            'reference' => $this->faker->unique()->regexify('[A-Z]{8}'),
            'status'    => 'settled',
            'notes'     => null,
            'card_type' => $this->faker->creditCardType,
            'last_four' => $this->faker->numberBetween(1000, 9999),
            'meta'      => null,
        ];
    }
}

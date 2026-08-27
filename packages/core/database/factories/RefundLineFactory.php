<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\RefundLine;
use Lunar\Core\Models\Transaction;

class RefundLineFactory extends BaseFactory
{
    protected $model = RefundLine::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'transaction_id' => Transaction::factory(),
            'order_line_id' => OrderLine::factory(),
            'quantity' => 1,
            'amount' => $this->faker->numberBetween(100, 5000),
        ];
    }
}

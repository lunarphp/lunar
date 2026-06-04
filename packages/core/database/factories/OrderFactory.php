<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Order;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

class OrderFactory extends BaseFactory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $total = $this->faker->numberBetween(200, 25000);
        $taxTotal = intval(($total - 100) * .2);

        return [
            'channel_id' => Channel::factory(),
            'new_customer' => $this->faker->boolean,
            'user_id' => null,
            'status' => 'awaiting-payment',
            'reference' => $this->faker->unique()->regexify('[A-Z]{8}'),
            'sub_total' => $total - $taxTotal,
            'discount_total' => 0,
            'shipping_total' => 0,
            'tax_breakdown' => new TaxBreakdown,
            'tax_total' => $taxTotal,
            'total' => $total,
            'notes' => null,
            'currency_code' => 'GBP',
            'compare_currency_code' => 'GBP',
            'exchange_rate' => 1,
            'meta' => ['foo' => 'bar'],
        ];
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn () => ['status' => 'awaiting-payment']);
    }

    public function inProcess(): static
    {
        return $this->state(fn () => ['status' => 'in-process']);
    }

    public function shipped(): static
    {
        return $this->state(fn () => ['status' => 'shipped']);
    }

    public function complete(): static
    {
        return $this->state(fn () => ['status' => 'complete']);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}

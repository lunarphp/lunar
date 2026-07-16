<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
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
            'public_id' => (string) Str::ulid(),
            'channel_id' => Channel::factory(),
            'new_customer' => $this->faker->boolean,
            'user_id' => null,
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

    /**
     * A placed (live) order.
     */
    public function placed(): static
    {
        return $this->state(fn () => ['placed_at' => now()]);
    }

    /**
     * An open (un-archived) order — the default.
     */
    public function open(): static
    {
        return $this->state(fn () => ['closed_at' => null]);
    }

    /**
     * A closed (archived) order.
     */
    public function closed(): static
    {
        return $this->state(fn () => ['closed_at' => now()]);
    }
}

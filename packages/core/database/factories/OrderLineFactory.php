<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

class OrderLineFactory extends BaseFactory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => ProductVariant::factory(),
            'type' => 'physical',
            'requires_shipping' => fn (array $attributes) => ($attributes['type'] ?? 'physical') === 'physical',
            // Defaults to its requires_shipping value so existing tests (and an
            // explicit requires_shipping override) keep behaving as before.
            'requires_fulfilment' => fn (array $attributes) => $attributes['requires_shipping'] ?? (($attributes['type'] ?? 'physical') === 'physical'),
            'description' => $this->faker->sentence,
            'option' => $this->faker->word,
            'identifier' => Str::random(),
            'unit_price' => $this->faker->numberBetween(1, 5000),
            'unit_quantity' => 1,
            'quantity' => 1,
            'sub_total' => $this->faker->numberBetween(1, 5000),
            'discount_total' => $this->faker->numberBetween(1, 5000),
            'tax_breakdown' => new TaxBreakdown,
            'tax_total' => $this->faker->numberBetween(1, 5000),
            'total' => $this->faker->numberBetween(1, 5000),
            'notes' => null,
            'meta' => null,
        ];
    }
}

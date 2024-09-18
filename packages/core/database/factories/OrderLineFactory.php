<?php

namespace Lunar\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;

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

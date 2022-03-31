<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Order;
use GetCandy\Models\OrderLine;
use GetCandy\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderLineFactory extends Factory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        return [
            'order_id'         => Order::factory(),
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => 1,
            'type'             => 'physical',
            'description'      => $this->faker->sentence,
            'option'           => $this->faker->word,
            'identifier'       => Str::random(),
            'unit_price'       => $this->faker->numberBetween(1, 5000),
            'unit_quantity'    => 1,
            'quantity'         => 1,
            'sub_total'        => $this->faker->numberBetween(1, 5000),
            'discount_total'   => $this->faker->numberBetween(1, 5000),
            'tax_breakdown'    => [
                [
                    'description'       => 'VAT',
                    'total'      => 200,
                    'percentage' => 20,
                ],
            ],
            'tax_total' => $this->faker->numberBetween(1, 5000),
            'total'     => $this->faker->numberBetween(1, 5000),
            'notes'     => null,
            'meta'      => null,
        ];
    }
}

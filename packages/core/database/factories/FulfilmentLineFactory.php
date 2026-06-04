<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentLine;
use Lunar\Core\Models\OrderLine;

class FulfilmentLineFactory extends BaseFactory
{
    protected $model = FulfilmentLine::class;

    public function definition(): array
    {
        return [
            'fulfilment_id' => Fulfilment::factory(),
            'order_line_id' => OrderLine::factory(),
            'quantity' => 1,
        ];
    }
}

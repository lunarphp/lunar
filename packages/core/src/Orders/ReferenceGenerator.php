<?php

namespace Lunar\Core\Orders;

use Lunar\Core\Contracts\OrderReferenceGenerator as OrderReferenceGeneratorContract;
use Lunar\Core\Models\Order;

class ReferenceGenerator implements OrderReferenceGeneratorContract
{
    /**
     * {@inheritDoc}
     */
    public function generate(Order $order): string
    {
        $config = config('lunar.orders.reference_format', []);

        $reference = str_pad(
            $order->id,
            $config['length'] ?? 8,
            $config['padding_character'] ?? 0,
            $config['padding_direction'] ?? STR_PAD_LEFT
        );

        $prefix = $config['prefix'] ?? '';

        return "{$prefix}{$reference}";
    }
}

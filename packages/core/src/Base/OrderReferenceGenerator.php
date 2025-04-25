<?php

namespace Lunar\Base;

use Lunar\Models\Order;

class OrderReferenceGenerator implements OrderReferenceGeneratorInterface
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

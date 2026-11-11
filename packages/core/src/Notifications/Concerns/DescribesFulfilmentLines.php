<?php

namespace Lunar\Core\Notifications\Concerns;

use Lunar\Core\Models\Fulfilment;

/**
 * Shapes a fulfilment's lines into the rows the `fulfilment-lines` mail partial
 * renders, so every fulfilment-scoped notification describes its lines the
 * same way.
 */
trait DescribesFulfilmentLines
{
    /**
     * @return array<int, array{description: string, option: ?string, quantity: int}>
     */
    protected function fulfilmentLineRows(Fulfilment $fulfilment): array
    {
        $fulfilment->loadMissing('lines.orderLine');

        return $fulfilment->lines->map(fn ($line) => [
            'description' => (string) $line->orderLine?->description,
            'option' => $line->orderLine?->option,
            'quantity' => (int) $line->quantity,
        ])->values()->all();
    }

    protected function orderReference(Fulfilment $fulfilment): string
    {
        return $fulfilment->order->reference ?? (string) $fulfilment->order->id;
    }
}

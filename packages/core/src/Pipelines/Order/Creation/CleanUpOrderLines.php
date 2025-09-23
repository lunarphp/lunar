<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Order;

class CleanUpOrderLines
{
    /**
     * @param  Closure(OrderContract): mixed  $next
     */
    public function handle(OrderContract $order, Closure $next): mixed
    {
        /** @var Order $order */
        $cart = $order->cart;

        // Build a set of "signatures" that uniquely identify each cart line
        $cartSignatures = $cart->lines->map(function ($line) {
            return $this->signature($line->purchasable_id, (array) $line->meta, $line->quantity);
        })->toArray();

        $order->productLines->each(function ($orderLine) use ($cartSignatures) {
            $sig = $this->signature(
                $orderLine->purchasable_id,
                (array) $orderLine->meta,
                $orderLine->quantity,
            );

            if (! in_array($sig, $cartSignatures, true)) {
                $orderLine->delete();
            }
        });

        return $next($order);
    }

    private function signature(string $purchasableId, array $meta, int $qty): string
    {
        return md5(json_encode([
            'id' => $purchasableId,
            'meta' => collect($meta)->sortKeys()->toArray(),
            'qty' => $qty,
        ]));
    }
}

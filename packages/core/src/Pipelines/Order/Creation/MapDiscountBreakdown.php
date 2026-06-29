<?php

namespace Lunar\Core\Pipelines\Order\Creation;

use Closure;
use Lunar\Core\Models\Order;
use Lunar\Core\Utils\Arr;

class MapDiscountBreakdown
{
    /**
     * @param  Closure(Order):mixed  $next
     */
    public function handle(Order $order, Closure $next): mixed
    {
        /** @var Order $order */
        $cart = $order->cart;

        $cartLinesMappedToOrderLines = [];

        foreach ($order->lines as $orderLine) {
            $cartLine = $cart->lines->first(function ($cartLine) use ($orderLine) {
                $diff = Arr::diff($cartLine->meta, $orderLine->meta);

                return empty($diff->new) &&
                    empty($diff->edited) &&
                    empty($diff->removed) &&
                    $cartLine->purchasable_type == $orderLine->purchasable_type &&
                    $cartLine->purchasable_id == $orderLine->purchasable_id;
            });

            if ($cartLine) {
                $cartLinesMappedToOrderLines[$cartLine->id] = $orderLine;
            }
        }

        $discountBreakdown = ($cart->discountBreakdown ?? collect())->map(function ($discount) use ($cartLinesMappedToOrderLines) {
            return (object) [
                'discount_id' => $discount->discount->id,
                'lines' => $discount->lines->map(function ($discountLine) use ($cartLinesMappedToOrderLines) {
                    return (object) [
                        'quantity' => $discountLine->quantity,
                        'line' => $cartLinesMappedToOrderLines[$discountLine->line->id],
                    ];
                }),
                'total' => $discount->price,
            ];
        })->values()->all();

        $order->update([
            'discount_breakdown' => $discountBreakdown,
        ]);

        return $next($order);
    }
}

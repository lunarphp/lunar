<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Utils\Arr;

class CreateOrderLines
{
    /**
     * @return mixed
     */
    public function handle(Order $order, Closure $next)
    {
        if (! $order->id) {
            $order->save();
        }

        $cart = $order->cart;

        $cart->recalculate();

        foreach ($cart->lines as $cartLine) {
            $orderLine = $order->lines->first(function ($line) use ($cartLine) {
                $diff = Arr::diff($line->meta, $cartLine->meta);

                return empty($diff->new) &&
                    empty($diff->edited) &&
                    empty($diff->removed) &&
                    $line->purchasable_type == $cartLine->purchasable_type &&
                    $line->purchasable_id == $cartLine->purchasable_id;
            }) ?: new OrderLine;

            $orderLine->fill([
                'order_id' => $order->id,
                'purchasable_type' => $cartLine->purchasable_type,
                'purchasable_id' => $cartLine->purchasable_id,
                'type' => $cartLine->purchasable->getType(),
                'description' => $cartLine->purchasable->getDescription(),
                'option' => $cartLine->purchasable->getOption(),
                'identifier' => $cartLine->purchasable->getIdentifier(),
                'unit_price' => $cartLine->unitPrice->value,
                'unit_quantity' => $cartLine->purchasable->getUnitQuantity(),
                'quantity' => $cartLine->quantity,
                'sub_total' => $cartLine->subTotal->value,
                'discount_total' => $cartLine->discountTotal?->value,
                'tax_breakdown' => $cartLine->taxBreakdown,
                'tax_total' => $cartLine->taxAmount->value,
                'total' => $cartLine->total->value,
                'notes' => null,
                'meta' => $cartLine->meta,
            ])->save();
        }

        return $next($order->refresh());
    }
}

<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Illuminate\Support\Facades\App;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Contracts\OrderLine as OrderLineContract;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;

class CreateOrderLines
{
    /**
     * @param  Closure(OrderContract): mixed  $next
     * @return mixed
     */
    public function handle(OrderContract $order, Closure $next): mixed
    {
        /** @var Order $order */
        if (! $order->id) {
            $order->save();
        }

        $cart = $order->cart;

        $cart->recalculate();

        foreach ($cart->lines as $cartLine) {
            /** @var OrderLine $orderLine */
            $orderLine = $order->lines->first(function ($line) use ($cartLine) {
                return $line->purchasable_id == $cartLine->purchasable_id &&
                    $line->purchasable_type == $cartLine->purchasable_type;
            }) ?: App::make(OrderLineContract::class);

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

<?php

namespace Lunar\Core\Pipelines\Order\Creation;

use Closure;
use Illuminate\Support\Facades\App;
use Lunar\Core\Contracts\Actions\Fulfilment\ResolvesCartFulfilment;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Utils\Arr;

class CreateOrderLines
{
    public function __construct(
        protected ResolvesCartFulfilment $resolveCartFulfilment,
    ) {}

    /**
     * @param  Closure(Order):mixed  $next
     */
    public function handle(Order $order, Closure $next): mixed
    {
        /** @var Order $order */
        if (! $order->id) {
            $order->save();
        }

        $cart = $order->cart;

        $cart->recalculate();

        $fulfilment = $this->resolveCartFulfilment->execute($cart);

        foreach ($cart->lines as $cartLine) {
            $fulfilmentMethod = $fulfilment->forLine($cartLine)?->getKey();

            /** @var OrderLine $orderLine */
            $orderLine = $order->lines->whereNull('parent_line_id')->first(function ($line) use ($cartLine, $fulfilmentMethod) {
                $diff = Arr::diff($line->meta, $cartLine->meta);

                return empty($diff->new) &&
                    empty($diff->edited) &&
                    empty($diff->removed) &&
                    $line->purchasable_type == $cartLine->purchasable_type &&
                    $line->purchasable_id == $cartLine->purchasable_id &&
                    $line->fulfilment_method === $fulfilmentMethod;
            }) ?: App::make(OrderLine::class);

            $orderLine->fill([
                'order_id' => $order->id,
                'purchasable_type' => $cartLine->purchasable_type,
                'purchasable_id' => $cartLine->purchasable_id,
                'type' => $cartLine->purchasable->getType(),
                'requires_shipping' => $cartLine->purchasable->isShippable(),
                'requires_fulfilment' => $cartLine->purchasable->requiresFulfilment(),
                'fulfilment_method' => $fulfilmentMethod,
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

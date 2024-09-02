<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Illuminate\Support\Facades\App;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Contracts\OrderLine as OrderLineContract;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;

class CreateShippingLine
{
    /**
     * @param  Closure(OrderContract): mixed  $next
     * @return Closure
     */
    public function handle(OrderContract $order, Closure $next): mixed
    {
        /** @var Order $order */
        $cart = $order->cart->recalculate();

        // If we have a shipping address with a shipping option.
        if (($shippingAddress = $cart->shippingAddress) &&
            ($shippingOption = $cart->getShippingOption())
        ) {
            /** @var OrderLine $shippingLine */
            $shippingLine = $order->lines->first(function ($orderLine) use ($shippingOption) {
                return $orderLine->type == 'shipping' &&
                    $orderLine->purchasable_type == ShippingOption::class &&
                    $orderLine->identifier == $shippingOption->getIdentifier();
            }) ?: App::make(OrderLineContract::class);

            $shippingLine->fill([
                'order_id' => $order->id,
                'purchasable_type' => ShippingOption::class,
                'purchasable_id' => 1,
                'type' => 'shipping',
                'description' => $shippingOption->getName(),
                'option' => $shippingOption->getOption(),
                'identifier' => $shippingOption->getIdentifier(),
                'unit_price' => $shippingOption->price->value,
                'unit_quantity' => $shippingOption->getUnitQuantity(),
                'quantity' => 1,
                'sub_total' => $shippingAddress->shippingSubTotal->value,
                'discount_total' => $shippingAddress->shippingSubTotal->discountTotal?->value ?: 0,
                'tax_breakdown' => $shippingAddress->taxBreakdown,
                'tax_total' => $shippingAddress->shippingTaxTotal->value,
                'total' => $shippingAddress->shippingTotal->value,
                'notes' => null,
                'meta' => $shippingOption->meta,
            ])->save();
        }

        return $next($order->refresh());
    }
}

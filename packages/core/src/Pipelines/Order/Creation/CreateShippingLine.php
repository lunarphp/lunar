<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;

class CreateShippingLine
{
    /**
     * @return Closure
     */
    public function handle(Order $order, Closure $next)
    {
        $cart = $order->cart->recalculate();

        // If we have a shipping address with a shipping option.
        if (($shippingAddress = $cart->shippingAddress) &&
            ($shippingOption = $cart->getShippingOption())
        ) {
            $shippingLine = $order->lines->first(function ($orderLine) use ($shippingOption) {
                return $orderLine->type == 'shipping' &&
                    $orderLine->purchasable_type == ShippingOption::class &&
                    $orderLine->identifier == $shippingOption->getIdentifier();
            }) ?: new OrderLine;

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

<?php

namespace Lunar\Core\Pipelines\Order\Creation;

use Closure;
use Illuminate\Support\Facades\App;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;

class CreateShippingLine
{
    /**
     * @param  Closure(Order):mixed  $next
     */
    public function handle(Order $order, Closure $next): mixed
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
            }) ?: App::make(OrderLine::class);

            $shippingLine->fill([
                'order_id' => $order->id,
                'purchasable_type' => ShippingOption::class,
                'purchasable_id' => 1,
                'type' => 'shipping',
                'requires_shipping' => false,
                'requires_fulfilment' => false,
                'description' => $shippingOption->getName(),
                'option' => $shippingOption->getOption(),
                'identifier' => $shippingOption->getIdentifier(),
                'unit_price' => $shippingOption->price->value,
                'unit_quantity' => $shippingOption->getUnitQuantity(),
                'quantity' => 1,
                'sub_total' => $shippingAddress->shippingSubTotal->value,
                'discount_total' => 0,
                'tax_breakdown' => $shippingAddress->taxBreakdown,
                'tax_total' => $shippingAddress->shippingTaxTotal->value,
                'total' => $shippingAddress->shippingTotal->value,
                'notes' => null,
                // Persist the chosen option's `collect` flag onto the line
                // snapshot so the `collection` fulfilment method can claim its
                // lines without re-resolving the option at order time.
                'meta' => array_merge($shippingOption->meta ?? [], [
                    'collect' => $shippingOption->collect,
                ]),
            ])->save();
        }

        return $next($order->refresh());
    }
}

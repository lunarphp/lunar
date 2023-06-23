<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class CreateOrderAddresses
{
    /**
     * @return Closure
     */
    public function handle(Order $order, Closure $next)
    {
        $orderAddresses = $order->addresses;

        foreach ($order->cart->addresses as $address) {
            $addressModel = $orderAddresses->first(function ($orderAddress) use ($address) {
                return $orderAddress->type == $address->type &&
                    $orderAddress->postcode == $address->postcode;
            }) ?: new OrderAddress;

            $addressModel->fill(
                collect(
                    $address->toArray()
                )->except(['cart_id', 'id'])->merge([
                    'order_id' => $order->id,
                ])->toArray()
            )->save();
        }

        return $next($order->refresh());
    }
}

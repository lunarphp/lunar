<?php

namespace Lunar\Pipelines\Order\Creation;

use Closure;
use Illuminate\Support\Facades\App;
use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Contracts\OrderAddress as OrderAddressContract;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class CreateOrderAddresses
{
    /**
     * @param  Closure(OrderContract): mixed  $next
     * @return Closure
     */
    public function handle(OrderContract $order, Closure $next): mixed
    {
        /** @var Order $order */
        $orderAddresses = $order->addresses;

        foreach ($order->cart->addresses as $address) {
            /** @var OrderAddress $addressModel */
            $addressModel = $orderAddresses->first(function ($orderAddress) use ($address) {
                return $orderAddress->type == $address->type &&
                    $orderAddress->postcode == $address->postcode;
            }) ?: App::make(OrderAddressContract::class);

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

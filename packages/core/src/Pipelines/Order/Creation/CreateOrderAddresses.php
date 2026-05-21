<?php

namespace Lunar\Core\Pipelines\Order\Creation;

use Closure;
use Illuminate\Support\Facades\App;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Contracts\OrderAddress as OrderAddressContract;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderAddress;

class CreateOrderAddresses
{
    /**
     * @param  Closure(OrderContract): mixed  $next
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

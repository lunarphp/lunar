<?php

namespace Lunar\Shipping\Observers;

use Lunar\Facades\DB;
use Lunar\Models\Order;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Facades\Shipping;

class OrderObserver
{
    public function updated(Order $order)
    {
        $this->updateShippingZone(
            $order
        );
    }

    public function created(Order $order)
    {
        $this->updateShippingZone(
            $order
        );
    }

    protected function updateShippingZone(Order $order)
    {
        $shippingAddress = $order->shippingAddress;
        if ($shippingAddress && $shippingAddress->postcode) {
            $postcodeLookup = new PostcodeLookup(
                $shippingAddress->country,
                $shippingAddress->postcode
            );

            $shippingZones = Shipping::zones()->postcode($postcodeLookup)->get();

            if ($shippingZone = $shippingZones->first()) {
                 DB::table(
                     config('lunar.database.table_prefix').'order_shipping_zone'
                 )->insert([
                     'order_id' => $order->id,
                     'shipping_zone_id' => $shippingZone->id,
                     'created_at' => now(),
                     'updated_at' => now(),
                 ]);
                $meta = (array) $order->meta;
                $meta['shipping_zone'] = $shippingZone->name;
                $order->meta = $meta;
                $order->saveQuietly();
            }
        }
    }

    /**
     * Called when we're about to index the order.
     **/
    public function indexing(Order $order)
    {
        $order->addSearchableAttribute('shipping_zone', $order->meta?->shipping_zone ?? null);
    }
}

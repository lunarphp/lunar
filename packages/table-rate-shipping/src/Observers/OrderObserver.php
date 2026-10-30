<?php

namespace Lunar\Shipping\Observers;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Order;
use Lunar\Shipping\DataTransferObjects\PostcodeLookup;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingZone;

class OrderObserver
{
    public function updated(Order $order): void
    {
        $this->updateShippingZone(
            $order
        );
    }

    public function created(Order $order): void
    {
        $this->updateShippingZone(
            $order
        );
    }

    protected function updateShippingZone(Order $order): void
    {
        $shippingAddress = $order->shippingAddress ?: $order->cart?->shippingAddress;

        if ($shippingAddress && $shippingAddress->postcode) {
            $postcodeLookup = new PostcodeLookup(
                $shippingAddress->country,
                $shippingAddress->postcode
            );

            $shippingZones = Shipping::zones()->postcode($postcodeLookup)->get();

            if ($shippingZone = $this->mostSpecific($shippingZones)) {
                Order::withoutSyncingToSearch(function () use ($order, $shippingZone) {
                    $order->shippingZone()->sync([$shippingZone->id]);
                });
                $meta = (array) $order->meta;
                $meta['shipping_zone'] = $shippingZone->name;
                $order->meta = $meta;
                $order->saveQuietly();
            }
        }
    }

    /**
     * The one zone to record against the order.
     *
     * The resolver returns every zone the address falls in, because rates hang
     * off all of them: a postcode zone and the country zone it sits inside
     * both match, and so does an unrestricted zone. It runs an OR of EXISTS
     * subqueries with no ORDER BY, so which comes first is the database's
     * choice, and on MySQL it is the country zone. Taking first() therefore
     * recorded the catch-all against every order. The zone the order was
     * actually rated in is the most specific match, so prefer postcodes, then
     * states, then countries, and only then an unrestricted zone.
     *
     * @param  Collection<int, ShippingZone>  $zones
     */
    protected function mostSpecific(Collection $zones): ?ShippingZone
    {
        $precedence = ['postcodes' => 0, 'states' => 1, 'countries' => 2, 'unrestricted' => 3];

        // Two sorts, not one: sortBy is stable, so sorting by id first and
        // then by precedence keeps ids ascending within a type, which makes
        // the pick deterministic when two zones of the same type match.
        return $zones
            ->sortBy('id')
            ->sortBy(fn (ShippingZone $zone) => $precedence[$zone->type] ?? PHP_INT_MAX)
            ->first();
    }

    /**
     * Called when we're about to index the order.
     **/
    public function indexing(Order $order): void
    {
        /** @var Order $order */
        $order->addSearchableAttribute('shipping_zone', $order->meta?->shipping_zone ?? null);
    }
}

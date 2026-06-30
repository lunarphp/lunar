<?php

namespace Lunar\Core\Actions\Fulfilment;

use Lunar\Core\Contracts\Actions\Fulfilment\AddsFulfilmentTracking;
use Lunar\Core\Contracts\CarrierManifest;
use Lunar\Core\Exceptions\FulfilmentException;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\FulfilmentTracking;

/**
 * Add a tracking reference to a fulfilment. A fulfilment can carry several, added
 * at ship time or afterwards as carrier details come through.
 */
class AddFulfilmentTracking implements AddsFulfilmentTracking
{
    public function __construct(
        protected CarrierManifest $carriers,
    ) {}

    public function execute(Fulfilment $fulfilment, array $attributes): FulfilmentTracking
    {
        /** @var Fulfilment $fulfilment */
        $attributes = array_intersect_key(
            $attributes,
            array_flip(['carrier', 'tracking_number', 'tracking_url', 'shipping_method']),
        );

        if (! filled($attributes['tracking_number'] ?? null)
            && ! filled($attributes['tracking_url'] ?? null)
            && ! filled($attributes['shipping_method'] ?? null)) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_tracking_empty'));
        }

        $carrier = $this->carriers->get($attributes['carrier'] ?? null);

        if (filled($attributes['tracking_number'] ?? null) && $carrier
            && ! $carrier->validateTrackingNumber($attributes['tracking_number'])) {
            throw new FulfilmentException(__('lunar::exceptions.fulfilment_tracking_invalid_number', [
                'carrier' => $carrier->getName(),
            ]));
        }

        /** @var FulfilmentTracking */
        return $fulfilment->trackings()->create($attributes);
    }
}

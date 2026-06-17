<?php

namespace Lunar\Core\Shipping\Carriers;

class Dpd extends Carrier
{
    public function getKey(): string
    {
        return 'dpd';
    }

    public function getName(): string
    {
        return 'DPD';
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [
            'next-day' => 'lunar::shipping.services.dpd.next-day',
            'two-day' => 'lunar::shipping.services.dpd.two-day',
            'classic-europe' => 'lunar::shipping.services.dpd.classic-europe',
        ];
    }

    protected function trackingUrlTemplate(): ?string
    {
        return 'https://track.dpd.co.uk/parcels/{tracking_number}';
    }
}

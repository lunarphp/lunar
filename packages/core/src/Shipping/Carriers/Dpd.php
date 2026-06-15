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
            'Next Day',
            'Two Day',
            'Classic (Europe)',
        ];
    }

    protected function trackingUrlTemplate(): ?string
    {
        return 'https://track.dpd.co.uk/parcels/{tracking_number}';
    }
}

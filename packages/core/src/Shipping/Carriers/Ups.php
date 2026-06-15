<?php

namespace Lunar\Core\Shipping\Carriers;

class Ups extends Carrier
{
    public function getKey(): string
    {
        return 'ups';
    }

    public function getName(): string
    {
        return 'UPS';
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [
            'Standard',
            'Express',
            'Express Saver',
        ];
    }

    protected function trackingUrlTemplate(): ?string
    {
        return 'https://www.ups.com/track?tracknum={tracking_number}';
    }
}

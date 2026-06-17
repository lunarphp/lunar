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
            'standard' => 'lunar::shipping.services.ups.standard',
            'express' => 'lunar::shipping.services.ups.express',
            'express-saver' => 'lunar::shipping.services.ups.express-saver',
        ];
    }

    protected function trackingUrlTemplate(): ?string
    {
        return 'https://www.ups.com/track?tracknum={tracking_number}';
    }
}

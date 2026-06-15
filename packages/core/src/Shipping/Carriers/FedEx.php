<?php

namespace Lunar\Core\Shipping\Carriers;

class FedEx extends Carrier
{
    public function getKey(): string
    {
        return 'fedex';
    }

    public function getName(): string
    {
        return 'FedEx';
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [
            'Priority',
            'Economy',
        ];
    }

    protected function trackingUrlTemplate(): ?string
    {
        return 'https://www.fedex.com/fedextrack/?trknbr={tracking_number}';
    }
}

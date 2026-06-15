<?php

namespace Lunar\Core\Shipping\Carriers;

class RoyalMail extends Carrier
{
    public function getKey(): string
    {
        return 'royal-mail';
    }

    public function getName(): string
    {
        return 'Royal Mail';
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [
            'Tracked 24',
            'Tracked 48',
            'Special Delivery Guaranteed',
            'International Tracked',
        ];
    }

    protected function trackingUrlTemplate(): ?string
    {
        return 'https://www.royalmail.com/track-your-item#/tracking-results/{tracking_number}';
    }
}

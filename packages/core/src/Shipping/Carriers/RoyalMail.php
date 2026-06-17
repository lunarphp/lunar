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
            'tracked-24' => 'lunar::shipping.services.royal-mail.tracked-24',
            'tracked-48' => 'lunar::shipping.services.royal-mail.tracked-48',
            'special-delivery-guaranteed' => 'lunar::shipping.services.royal-mail.special-delivery-guaranteed',
            'international-tracked' => 'lunar::shipping.services.royal-mail.international-tracked',
        ];
    }

    protected function trackingUrlTemplate(): ?string
    {
        return 'https://www.royalmail.com/track-your-item#/tracking-results/{tracking_number}';
    }
}

<?php

namespace Lunar\Core\Manifests;

use Lunar\Core\Contracts\HoldReasonManifest as HoldReasonManifestContract;

class HoldReasonManifest extends ReasonManifest implements HoldReasonManifestContract
{
    /**
     * {@inheritDoc}
     */
    protected function defaults(): array
    {
        return [
            'awaiting-payment' => 'Awaiting payment',
            'out-of-stock' => 'Inventory out of stock',
            'incorrect-address' => 'Incorrect address',
            'high-risk' => 'High risk of fraud',
            'other' => 'Other',
        ];
    }
}

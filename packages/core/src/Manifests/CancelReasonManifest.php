<?php

namespace Lunar\Core\Manifests;

use Lunar\Core\Contracts\CancelReasonManifest as CancelReasonManifestContract;

class CancelReasonManifest extends ReasonManifest implements CancelReasonManifestContract
{
    /**
     * {@inheritDoc}
     */
    protected function defaults(): array
    {
        return [
            'customer' => 'Customer changed/cancelled order',
            'items-unavailable' => 'Items unavailable',
            'fraud' => 'Fraudulent order',
            'declined' => 'Payment declined',
            'other' => 'Other',
        ];
    }
}

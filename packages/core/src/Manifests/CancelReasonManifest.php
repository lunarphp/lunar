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
            'customer' => 'lunar::reasons.cancel.customer',
            'items-unavailable' => 'lunar::reasons.cancel.items-unavailable',
            'fraud' => 'lunar::reasons.cancel.fraud',
            'declined' => 'lunar::reasons.cancel.declined',
            'other' => 'lunar::reasons.cancel.other',
        ];
    }
}

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
            'awaiting-payment' => 'lunar::reasons.hold.awaiting-payment',
            'out-of-stock' => 'lunar::reasons.hold.out-of-stock',
            'incorrect-address' => 'lunar::reasons.hold.incorrect-address',
            'high-risk' => 'lunar::reasons.hold.high-risk',
            'other' => 'lunar::reasons.hold.other',
        ];
    }
}

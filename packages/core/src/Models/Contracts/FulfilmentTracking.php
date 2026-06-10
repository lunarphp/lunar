<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\Contracts\ShippingCarrier;

interface FulfilmentTracking
{
    /**
     * Return the fulfilment relationship.
     */
    public function fulfilment(): BelongsTo;

    /**
     * Resolve the registered carrier for this tracking, if any.
     */
    public function carrier(): ?ShippingCarrier;
}

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

    /**
     * The translated label for the selected shipping method, if any.
     */
    public function shippingMethodLabel(): ?string;

    /**
     * Remove this tracking reference from its fulfilment.
     */
    public function remove(): void;
}

<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\StockReservation;

interface CommitsReservation
{
    /**
     * Convert a reservation to a commitment at order placement: free the
     * reserved quantity (the order line now carries the commitment via the
     * committed recompute). Idempotent. No direct `committed` write — that is
     * derived from the order book.
     */
    public function execute(StockReservation $reservation): StockReservation;
}

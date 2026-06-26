<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Contracts\StockReservation as StockReservationContract;
use Lunar\Core\Models\StockReservation;

interface ReleasesReservation
{
    /**
     * Release a reservation, returning its quantity to availability. Idempotent
     * — a no-op if already released or committed.
     */
    public function execute(StockReservationContract $reservation): StockReservation;
}

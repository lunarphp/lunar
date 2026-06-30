<?php

namespace Lunar\Core\Actions\Products;

use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockReserved;
use Lunar\Core\Contracts\Actions\Products\ReleasesReservation;
use Lunar\Core\Models\StockReservation;

/**
 * Release a reservation, returning its quantity to availability. A no-op if the
 * reservation is already released or committed.
 */
class ReleaseReservation implements ReleasesReservation
{
    public function __construct(
        protected RecomputesStockReserved $recomputeReserved,
    ) {}

    public function execute(StockReservation $reservation): StockReservation
    {
        /** @var StockReservation $reservation */
        if (filled($reservation->released_at) || filled($reservation->committed_at)) {
            return $reservation;
        }

        return DB::transaction(function () use ($reservation) {
            $reservation->forceFill(['released_at' => now()])->save();

            $this->recomputeReserved->execute($reservation->loadMissing('variant')->variant);

            return $reservation;
        });
    }
}

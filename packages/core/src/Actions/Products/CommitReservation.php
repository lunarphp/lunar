<?php

namespace Lunar\Core\Actions\Products;

use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Products\CommitsReservation;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockReserved;
use Lunar\Core\Models\StockReservation;

/**
 * Convert a reservation to a commitment at order placement: stamp it committed
 * and free the reserved quantity. The order line carries the commitment from
 * here (via the committed recompute), so `committed` is not written directly.
 * A no-op if already released or committed.
 */
class CommitReservation implements CommitsReservation
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
            $reservation->forceFill(['committed_at' => now()])->save();

            $this->recomputeReserved->execute($reservation->loadMissing('variant')->variant);

            return $reservation;
        });
    }
}

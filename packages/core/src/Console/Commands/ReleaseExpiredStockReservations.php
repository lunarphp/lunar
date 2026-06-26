<?php

namespace Lunar\Core\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Core\Contracts\Actions\Products\ReleasesReservation;
use Lunar\Core\Models\StockReservation;

class ReleaseExpiredStockReservations extends Command
{
    protected $signature = 'lunar:stock:release-expired';

    protected $description = 'Release stock reservations whose hold has expired, returning the quantity to availability.';

    public function handle(ReleasesReservation $releaseReservation): int
    {
        $released = 0;

        StockReservation::query()
            ->whereNull('released_at')
            ->whereNull('committed_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($reservations) use ($releaseReservation, &$released) {
                foreach ($reservations as $reservation) {
                    $releaseReservation->execute($reservation);
                    $released++;
                }
            });

        $this->info("Released {$released} expired reservation(s).");

        return self::SUCCESS;
    }
}

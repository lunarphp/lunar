<?php

namespace Lunar\Drivers;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Base\DataTransferObjects\StockInfo;
use Lunar\Base\Purchasable;
use Lunar\Base\ReservesStock;
use Lunar\Base\StockDriver;
use Lunar\Exceptions\Stock\NotReservedException;
use Lunar\Models\ProductVariant;
use Lunar\Models\StockReservation;

class SimpleStockDriver implements StockDriver
{
    /**
     * Get the available stock information, after deducting reservations.
     */
    public function availableStock(Purchasable $purchasable): StockInfo
    {
        // SimpleStock driver only supports ProductVariants
        $this->checkIsVariant($purchasable);

        $reservedCount = StockReservation::where('variant_id', '=', $purchasable->id)
            ->where('expires_at', '>', now())
            ->sum('quantity');

        $stockInfo = new StockInfo($purchasable->stock, $purchasable->backorder);

        $stockInfo->stock -= $reservedCount;

        if ($stockInfo->stock < 0) {
            // Backorder
            $stockInfo->backorder += $stockInfo->stock;
            $stockInfo->stock = 0;
        }

        return $stockInfo;
    }

    /**
     * Check if we can reserve the required quantity.
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function checkStock(ReservesStock $line)
    {
        // SimpleStock driver only supports ProductVariants
        $this->checkIsVariant($line->purchasable);

        // Ensure we have enough stock to reserve (stock + backorder - reserved)
        $reservedCount = StockReservation::where('variant_id'.'=', $line->purchasable->id)
            ->where('expires_at', '>', now())
            ->whereNot(function (Builder $query) use ($line) {
                $query->where('stockable_type', '=', $line::class)
                    ->where('stockable_id', '=', $line->id);
            })
            ->sum('quantity');

        $totalAvailable = $line->purchasable->stock + $line->purchasable->backorder - $reservedCount;

        return $totalAvailable >= $line->quantity;
    }

    /**
     * Reserve stock for the cart/order line.
     */
    public function reserveStock(ReservesStock $line, array $location = null): bool
    {
        // Check if we have enough stock available to reserve
        throw_if(! $this->checkStock($line), new NotReservedException('Cannot reserve stock for purchasable.'));

        $reservationDuration = config('lunar.stock.reservation_duration', 30);

        // Add reservation
        $test = StockReservation::updateOrCreate(
            ['stockable_id' => $line->id, 'stockable_type' => $line::class, 'variant_id' => $line->purchasable->id],
            ['quantity' => $line->quantity, 'expires_at' => now()->addMinutes($reservationDuration)]
        );

        return true;
    }

    /**
     * Release stock for the cart/order line.
     */
    public function releaseStock(ReservesStock $line, int $quantity = null): bool
    {
        $reservation = StockReservation::where('stockable_type', '=', $line::class)
            ->where('stockable_id', '=', $line->id)
            ->first();

        if (! $reservation) {
            return false;
        }

        if (! $quantity) {
            $quantity = $reservation->quantity;
        }

        if ($reservation->quantity > $quantity) {
            $reservation->quantity -= $quantity;
            $reservation->save();

            return true;
        }

        $reservation->expires_at = now();
        $reservation->save();

        return true;
    }

    /**
     * Dispatch stock for the order line.
     */
    public function dispatchStock(ReservesStock $line, int $quantity = null): bool
    {
        // Remove/expire the reservation and decrement from stock figure

        if (! $quantity) {
            $quantity = $line->quantity;
        }

        if (! $this->releaseStock($line, $quantity)) {
            throw new NotReservedException("Cannot release stock that isn't reserved");
        }

        $line->purchasable->stock -= $quantity;

        if ($line->purchasable->stock < 0) {
            // Backorder
            $line->purchasable->backorder += $line->purchasable->stock;
            $line->purchasable->stock = 0;
        }

        $line->purchasable->save();

        return true;
    }

    public function transferReservation(ReservesStock $line1, ReservesStock $line2): bool
    {
        foreach ($line1->stockReservations as $reservation) {
            $reservation->stockable_type = $line2::class;
            $reservation->stockable_id = $line2->id;
            $reservation->save();
        }

        return true;
    }

    /**
     * @throws \Throwable
     */
    private function checkIsVariant(Purchasable $purchasable): void
    {
        throw_if(
            $purchasable::class !== ProductVariant::class,
            new Exception('Purchasable must be of type '.ProductVariant::class.' for SimpleStock driver, '.$purchasable::class.' given.')
        );
    }
}

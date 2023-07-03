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
        // TODO:
        // 1. Check we're using a ProductVariant
        // 2. Get stock + backorder
        // 3. Deduct reserved (StockReservation)

        return new StockInfo;
    }

    /**
     * Get the reserved stock information.
     */
    public function reservedStock(Purchasable $purchasable): StockInfo
    {
        // TODO:
        // 1. Check we're using a ProductVariant
        // 2. Deduct reserved (StockReservation)

        return new StockInfo;
    }

    /**
     * Check if we can reserve the required quantity.
     *
     *
     * @return bool
     */
    public function checkStock(ReservesStock $line, int $quantity)
    {
        // SimpleStock driver only supports ProductVariants
        $this->checkIsVariant($line->purchasable);

        // Ensure we have enough stock to reserve (stock + backorder - reserved)
        $reservedCount = StockReservation::where('variant_id'.'=', $line->purchasable->id)
            ->where('expires_at', '<', now())
            ->whereNot(function (Builder $query) use ($line) {
                $query->where('stockable_type', '=', $line::class)
                    ->where('stockable_id', '=', $line->id);
            })
            ->sum('quantity');

        $totalAvailable = $line->purchasable->stock + $line->purchasable->backorder - $reservedCount;

        return $totalAvailable >= $quantity;
    }

    /**
     * Reserve stock for the cart/order line.
     */
    public function reserveStock(ReservesStock $line, string $location = null): bool
    {
        // Check if we have enough stock available to reserve
        if (! $this->checkStock($line, $line->quantity)) {
            return false;
        }

        // Add reservation
        $test = StockReservation::updateOrCreate(
            ['stockable_id' => $line->id, 'stockable_type' => $line::class, 'variant_id' => $line->purchasable->id],
            ['quantity' => $line->quantity, 'expires_at' => now()]
        );

        return true;
    }

    /**
     * Release stock for the cart/order line.
     *
     * @param  int  $quantity
     */
    public function releaseStock(ReservesStock $line, int $quantity = null): bool
    {
        $reservation = StockReservation::where('stockable_type', '=', $line::class)
            ->where('stockable_id', '=', $line->id)
            ->first();

        if (! $reservation) {
            return true;
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
     *
     * @param  int  $quantity
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

    private function checkIsVariant(Purchasable $purchasable): void
    {
        if ($purchasable::class !== ProductVariant::class) {
            throw new Exception('Purchasable must be of type '.ProductVariant::class.' for SimpleStock driver, '.$purchasable::class.' given.');
        }
    }
}

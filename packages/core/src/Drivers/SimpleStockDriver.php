<?php

namespace Lunar\Drivers;

use Exception;
use Lunar\Base\DataTransferObjects\StockInfo;
use Lunar\Base\Purchasable;
use Lunar\Base\ReservesStock;
use Lunar\Base\StockDriver;
use Lunar\Exceptions\Stock\NotReservedException;
use Lunar\Models\ProductVariant;
use Lunar\Models\StockReservation;use function Symfony\Component\String\u;

class SimpleStockDriver implements StockDriver
{
    /**
     * Get the available stock information, after deducting reservations.
     *
     * @param  \Lunar\Base\Purchasable  $purchasable
     *
     * @return StockInfo
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
     *
     * @param  \Lunar\Base\Purchasable  $purchasable
     *
     * @return StockInfo
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
     * @param  \Lunar\Base\Purchasable  $purchasable
     * @param  int $quantity
     *
     * @return bool
     */
    public function checkStock(Purchasable $purchasable, int $quantity)
    {
        // SimpleStock driver only supports ProductVariants
        $this->checkIsVariant($purchasable);

        // Ensure we have enough stock to reserve (stock+backorder+reserved)
        $reservedCount = StockReservation::where('variant_id'. '=', $purchasable->id)
            ->where('expires_at', '<', now())
            ->sum('quantity');

        $totalAvailable = $purchasable->stock + $purchasable->backorder + $reservedCount;

        return ($totalAvailable >= $quantity);
    }

    /**
     * Reserve stock for the cart/order line.
     *
     * @param  ReservesStock $line
     * @param  string|null   $location
     *
     * @return bool
     */
    public function reserveStock(ReservesStock $line, string $location = null): bool
    {
        // Check if we have enough stock available to reserve
        if (! $this->checkStock($line->purchasable, $line->quantity)) {
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
     * @param  ReservesStock $line
     * @param int $quantity
     *
     * @return bool
     */
    public function releaseStock(ReservesStock $line, int $quantity = null): bool
    {
        $reservation = StockReservation::where('stockable_type', '=', $line::class)
            ->where('stockable_id', '=', $line->id)
            ->first();

        if (!$reservation) {
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
     * @param  ReservesStock $line
     * @param int $quantity
     *
     * @return bool
     */
    public function dispatchStock(ReservesStock $line, int $quantity = null): bool
    {
        // Remove/expire the reservation and decrement from stock figure

        if (! $quantity) {
            $quantity = $line->quantity;
        }

        If (! $this->releaseStock($line, $quantity)) {
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
            throw new Exception("Purchasable must be of type ".ProductVariant::class." for SimpleStock driver, ".$purchasable::class." given.");
        }
    }

}
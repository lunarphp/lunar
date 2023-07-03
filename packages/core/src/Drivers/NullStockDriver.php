<?php

namespace Lunar\Drivers;

use Lunar\Base\DataTransferObjects\StockInfo;
use Lunar\Base\Purchasable;
use Lunar\Base\ReservesStock;
use Lunar\Base\StockDriver;
use Lunar\Models\StockReservation;

class NullStockDriver implements StockDriver
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
        return true;
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
        return true;
    }
}

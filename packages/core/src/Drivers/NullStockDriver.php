<?php

namespace Lunar\Drivers;

use Lunar\Base\DataTransferObjects\StockInfo;
use Lunar\Base\Purchasable;
use Lunar\Base\ReservesStock;
use Lunar\Base\StockDriver;

class NullStockDriver implements StockDriver
{
    /**
     * Get the available stock information, after deducting reservations.
     */
    public function availableStock(Purchasable $purchasable): StockInfo
    {
        return new StockInfo;
    }

    /**
     * Get the reserved stock information.
     */
    public function reservedStock(Purchasable $purchasable): StockInfo
    {
        return new StockInfo;
    }

    /**
     * Check if we can reserve the required quantity.
     *
     *
     * @return bool
     */
    public function checkStock(Purchasable $purchasable, int $quantity)
    {
        return true;
    }

    /**
     * Reserve stock for the cart/order line.
     */
    public function reserveStock(ReservesStock $line, string $location = null): bool
    {
        return true;
    }

    /**
     * Release stock for the cart/order line.
     *
     * @param  int  $quantity
     */
    public function releaseStock(ReservesStock $line, int $quantity = null): bool
    {
        return true;
    }

    /**
     * Dispatch stock for the order line.
     *
     * @param  int  $quantity
     */
    public function dispatchStock(ReservesStock $line, int $quantity = null): bool
    {
        return true;
    }
}

<?php

namespace Lunar\Base;

use Lunar\Base\DataTransferObjects\StockInfo;
use Lunar\Models\CartLine;
use Lunar\Models\OrderLine;
use Lunar\Base\ReservesStock;

interface StockDriver
{
    /**
     * Get the available stock information, after deducting reservations.
     *
     * @param  \Lunar\Base\Purchasable  $purchasable
     *
     * @return StockInfo
     */
    public function availableStock(Purchasable $purchasable): StockInfo;

    /**
     * Get the reserved stock information.
     *
     * @param  \Lunar\Base\Purchasable  $purchasable
     *
     * @return StockInfo
     */
    public function reservedStock(Purchasable $purchasable): StockInfo;

    /**
     * Reserve stock for the cart/order line.
     *
     * @param  ReservesStock $line
     * @param  string|null   $location
     *
     * @return bool
     */
    public function reserveStock(ReservesStock $line, string $location = null): bool;

    /**
     * Release stock for the cart/order line.
     *
     * @param  ReservesStock $line
     * @param int $quantity
     *
     * @return bool
     */
    public function releaseStock(ReservesStock $line, int $quantity = null): bool;

    /**
     * Dispatch stock for the order line.
     *
     * @param  ReservesStock $line
     * @param int $quantity
     *
     * @return bool
     */
    public function dispatchStock(ReservesStock $line, int $quantity = null): bool;
}

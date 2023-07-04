<?php

namespace Lunar\Base;

use Lunar\Base\DataTransferObjects\StockInfo;

interface StockDriver
{
    /**
     * Get the available stock information, after deducting reservations.
     *
     * @param  \Lunar\Base\Purchasable  $purchasable
     */
    public function availableStock(Purchasable $purchasable): StockInfo;

    /**
     * Reserve stock for the cart/order line.
     */
    public function reserveStock(ReservesStock $line, array $location = null): bool;

    /**
     * Transfers stock from one model to another, e.g. Cart to Order.
     */
    public function transferReservation(ReservesStock $line1, ReservesStock $line2): bool;

    /**
     * Release stock for the cart/order line.
     *
     * @param  int  $quantity
     */
    public function releaseStock(ReservesStock $line, int $quantity = null): bool;

    /**
     * Dispatch stock for the order line.
     *
     * @param  int  $quantity
     */
    public function dispatchStock(ReservesStock $line, int $quantity = null): bool;
}

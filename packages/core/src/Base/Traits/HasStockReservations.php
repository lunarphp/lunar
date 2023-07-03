<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Lunar\Facades\Stock;
use Lunar\Models\StockReservation;

trait HasStockReservations
{
    public function reserveStock(array $location): bool
    {
        return Stock::reserveStock($this, $location);
    }

    public function releaseStock(int $quantity = null): bool
    {
        return Stock::releaseStock($this, $quantity);
    }

    public function dispatchStock(int $quantity = null): bool
    {
        return Stock::dispatchStock($this, $quantity);
    }

    public function stockReservations(): MorphMany
    {
        return $this->morphMany(StockReservation::class, 'stockable');
    }
}

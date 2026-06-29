<?php

namespace Lunar\Core\Contracts\Actions\Products;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockReservation;

interface ReservesStock
{
    /**
     * Place a (optionally time-boxed) hold against a variant and refresh the
     * `reserved` rollup. `$reference` is the holder — a Cart in the follow-on.
     */
    public function execute(
        ProductVariant $variant,
        int $quantity,
        ?DateTimeInterface $expiresAt = null,
        ?Model $reference = null,
        ?string $note = null,
    ): StockReservation;
}

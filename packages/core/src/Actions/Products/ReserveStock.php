<?php

namespace Lunar\Core\Actions\Products;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockReserved;
use Lunar\Core\Contracts\Actions\Products\ReservesStock;
use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockReservation;

/**
 * Place a hold against a variant. Global — reservations carry no location, since
 * a cart picks none; they reduce the variant's sellable `available`.
 */
class ReserveStock implements ReservesStock
{
    public function __construct(
        protected RecomputesStockReserved $recomputeReserved,
    ) {}

    public function execute(
        ProductVariantContract $variant,
        int $quantity,
        ?DateTimeInterface $expiresAt = null,
        ?Model $reference = null,
        ?string $note = null,
    ): StockReservation {
        /** @var ProductVariant $variant */
        return DB::transaction(function () use ($variant, $quantity, $expiresAt, $reference, $note) {
            $reservation = StockReservation::query()->create([
                'product_variant_id' => $variant->getKey(),
                'quantity' => $quantity,
                'expires_at' => $expiresAt,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'note' => $note,
            ]);

            $this->recomputeReserved->execute($variant);

            return $reservation;
        });
    }
}

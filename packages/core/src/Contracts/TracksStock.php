<?php

namespace Lunar\Core\Contracts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Models\StockReservation;

/**
 * Opt-in capability for a `Purchasable` that participates in stock commitment.
 *
 * "Sellable" (every `Purchasable`) and "stock-tracked" are separate concerns —
 * gift cards, digital downloads and services answer `canBeFulfilledAtQuantity()`
 * without tracking stock. A purchasable that *does* track stock implements this
 * capability; the cart / checkout / order-lifecycle hooks operate through it and
 * skip anything that does not.
 *
 * `ProductVariant` is the only built-in implementation (its default trait is
 * `Concerns\HasStock`, backed by the `StockLevel` engine). A custom stock-tracked
 * purchasable (event seats, a bundle, an external WMS) implements this with its
 * own storage — its stock rarely looks like `integer on_hand at a location`.
 */
interface TracksStock
{
    /**
     * Recompute committed stock from the order book after lifecycle activity
     * (placement, fulfilment create / ship / return / cancel, order cancel).
     *
     * Idempotent — recomputes from source, never applies deltas, so repeated
     * or out-of-order events converge to the same result.
     */
    public function syncStockCommitment(): void;

    /**
     * Place a (optionally time-boxed) hold against this purchasable, returning a
     * reservation handle the checkout can later release or commit. `$reference`
     * is the holder (a Cart). Built-in implementers return a `StockReservation`;
     * a custom purchasable returns its own handle satisfying the contract.
     */
    public function reserveStock(int $quantity, ?\DateTimeInterface $expiresAt = null, ?Model $reference = null, ?string $note = null): StockReservation;
}

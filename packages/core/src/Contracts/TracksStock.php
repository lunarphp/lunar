<?php

namespace Lunar\Core\Contracts;

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
}

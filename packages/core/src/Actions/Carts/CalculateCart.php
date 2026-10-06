<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Pipeline\Pipeline;
use Lunar\Core\Contracts\Actions\Carts\CalculatesCart;
use Lunar\Core\Contracts\Actions\Carts\HydratesCartTotals;
use Lunar\Core\Contracts\Actions\Carts\PersistsCartTotals;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Pipelines\Cart\Calculate;

/**
 * Decides how a cart gets its totals (spec 0076): the in-memory memo when it
 * is already calculated, the persisted snapshot when that is fresh and
 * complete, and the calculation pipeline otherwise. A pipeline run persists
 * its result so the next request can be served from the row.
 */
class CalculateCart implements CalculatesCart
{
    public function __construct(
        protected Pipeline $pipeline,
        protected HydratesCartTotals $hydratesCartTotals,
        protected PersistsCartTotals $persistsCartTotals,
    ) {}

    public function execute(Cart $cart, bool $force = false): Cart
    {
        /** @var Cart $cart */
        if (! $force && $cart->isCalculated()) {
            return $cart;
        }

        if (! $force && $cart->totalsAreFresh() && $this->snapshotCoversEveryLine($cart)) {
            return $this->hydratesCartTotals->execute($cart);
        }

        // Lines the pipeline itself writes (BuyXGetY reward lines) are part of
        // the calculation, not a change to it; the line observer skips
        // invalidation while this flag is set.
        $cart->setCalculating(true);

        try {
            $cart = $this->pipeline
                ->send($cart)
                ->through(config('lunar.cart.pipelines.cart', [Calculate::class]))
                ->thenReturn();
        } finally {
            $cart->setCalculating(false);
        }

        $cart->lines->each(fn (CartLine $line) => $line->cacheProperties());
        $cart->cacheProperties();

        $this->persistsCartTotals->execute($cart);

        return $cart;
    }

    /**
     * A line added by the pipeline after the snapshot was written (an automatic
     * reward) has no totals of its own yet; serving the snapshot would render
     * it without a price, so the pipeline runs and fills it in.
     */
    protected function snapshotCoversEveryLine(Cart $cart): bool
    {
        return $cart->lines->every(
            fn (CartLine $line) => $line->getAttribute('total') !== null
        );
    }
}

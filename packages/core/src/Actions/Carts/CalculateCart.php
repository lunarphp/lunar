<?php

namespace Lunar\Core\Actions\Carts;

use Illuminate\Pipeline\Pipeline;
use Lunar\Core\Contracts\Actions\Carts\CalculatesCart;
use Lunar\Core\Contracts\Actions\Carts\HydratesCartTotals;
use Lunar\Core\Contracts\Actions\Carts\PersistsCartTotals;
use Lunar\Core\Contracts\StepManifest;
use Lunar\Core\Enums\StepList;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Modifiers\CartLineModifiers;
use Lunar\Core\Modifiers\CartModifiers;

/**
 * Decides how a cart gets its totals (spec cart-totals-caching): the in-memory memo when it
 * is already calculated, the persisted snapshot when that is fresh and
 * complete, and the calculation pipeline otherwise. A pipeline run persists
 * its result so the next request can be served from the row.
 */
class CalculateCart implements CalculatesCart
{
    public function __construct(
        protected Pipeline $pipeline,
        protected StepManifest $steps,
        protected CartModifiers $cartModifiers,
        protected CartLineModifiers $cartLineModifiers,
        protected HydratesCartTotals $hydratesCartTotals,
        protected PersistsCartTotals $persistsCartTotals,
    ) {}

    public function execute(Cart $cart, bool $force = false): Cart
    {
        /** @var Cart $cart */
        if (! $force && $cart->isCalculated()) {
            return $cart;
        }

        $persistable = ! $cart->hasUnpersistedInputs();

        if (! $force && $persistable && $cart->totalsAreFresh() && $this->snapshotCoversEveryLine($cart)) {
            return $this->hydratesCartTotals->execute($cart);
        }

        // Lines the pipeline itself writes (BuyXGetY reward lines) are part of
        // the calculation, not a change to it; the line observer skips
        // invalidation while this flag is set. It also holds the cart's
        // context for the whole calculation.
        $calculating = $cart->setCalculating(true);

        try {
            $cart = $this->pipeline
                ->send($this->cartModifiers->calculating($cart))
                ->through($this->steps->for(StepList::CartCalculation)->all())
                ->thenReturn();

            if ($this->cartLineModifiers->getModifiers()->isNotEmpty()) {
                foreach ($cart->loadMissing('lines')->lines as $line) {
                    $this->cartLineModifiers->calculated($line);
                }
            }

            $cart = $this->cartModifiers->calculated($cart);
        } finally {
            $calculating->setCalculating(false);
        }

        $cart->lines->each(fn (CartLine $line) => $line->cacheProperties());
        $cart->cacheProperties();

        if ($persistable) {
            $this->persistsCartTotals->execute($cart);
        }

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

<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Events\Discounts\DiscountCreated;
use Lunar\Core\Events\Discounts\DiscountDeleted;
use Lunar\Core\Events\Discounts\DiscountUpdated;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Discount;

class DiscountObserver
{
    public function created(Discount $discount): void
    {
        DiscountCreated::dispatch($discount);
    }

    public function updated(Discount $discount): void
    {
        DiscountUpdated::dispatch($discount);
    }

    public function deleted(Discount $discount): void
    {
        DiscountDeleted::dispatch($discount);
    }

    /**
     * Handle the Discount "deleting" event.
     */
    public function deleting(Discount $discount): void
    {
        $discount->brands()->detach();
        $discount->channels()->detach();
        $discount->collections()->detach();
        $discount->customerGroups()->detach();
        $discount->customers()->detach();
        $discount->discountables()->delete();
        $discount->redemptions()->delete();
        $discount->coupons()->delete();

        // The lines it added for a reward on carts still being shopped. The
        // key is null on delete, so they would otherwise stay as ordinary lines
        // at full price (spec remove-unearned-reward-lines). A cart with a
        // completed order keeps its lines as history. Deleted one model at a
        // time so the line observer stales each cart's persisted totals.
        CartLine::query()
            ->with('cart')
            ->where('added_by_discount_id', $discount->id)
            ->whereDoesntHave('cart.completedOrders')
            ->chunkById(100, fn ($lines) => $lines->each(fn (CartLine $line) => $line->delete()));
    }
}

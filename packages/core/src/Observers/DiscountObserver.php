<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Events\Discounts\DiscountCreated;
use Lunar\Core\Events\Discounts\DiscountDeleted;
use Lunar\Core\Events\Discounts\DiscountUpdated;
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
        $discount->users()->detach();
    }
}

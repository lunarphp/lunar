<?php

namespace Lunar\Shipping\Traits;

use Illuminate\Database\Eloquent\Collection;
use Lunar\Shipping\Models\ShippingExclusionList;

/**
 * @property $shippingMethod
 */
trait ExcludesProducts
{
    public array $lists = [];

    public function mountExcludesProducts(): void
    {
        $this->lists = $this->shippingMethod->shippingExclusions->pluck('id')->toArray();
    }

    public function updateExcludedLists(): void
    {
        $this->shippingMethod->shippingExclusions()->sync($this->lists);
    }

    /**
     * Return the exclusions collection.
     */
    public function getExclusionListsProperty(): Collection
    {
        return ShippingExclusionList::all();
    }
}

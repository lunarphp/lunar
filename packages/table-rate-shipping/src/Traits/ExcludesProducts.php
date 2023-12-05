<?php

namespace Lunar\Shipping\Traits;

use Lunar\Shipping\Models\ShippingExclusionList;

trait ExcludesProducts
{
    public array $lists = [];

    public function mountExcludesProducts()
    {
        $this->lists = $this->shippingMethod->shippingExclusions->pluck('id')->toArray();
    }

    public function updateExcludedLists()
    {
        $this->shippingMethod->shippingExclusions()->sync($this->lists);
    }

    /**
     * Return the exclusions collection.
     *
     * @return void
     */
    public function getExclusionListsProperty()
    {
        return ShippingExclusionList::get();
    }
}

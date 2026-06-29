<?php

namespace Lunar\Core\Actions\Collections;

use Illuminate\Support\Collection as SupportCollection;
use Lunar\Core\Contracts\Actions\Collections\SortsProducts;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Currency;

class SortProducts implements SortsProducts
{
    public function __construct(
        protected SortProductsByPrice $sortProductsByPrice,
        protected SortProductsBySku $sortProductsBySku,
    ) {}

    /**
     * Execute the action.
     */
    public function execute(Collection $collection): SupportCollection
    {
        /** @var Collection $collection */
        [$sort, $direction] = explode(':', $collection->sort);

        return match ($sort) {
            'min_price' => $this->sortProductsByPrice->execute(
                $collection->products,
                Currency::getDefault(),
                $direction
            ),
            'sku' => $this->sortProductsBySku->execute(
                $collection->products,
                $direction
            ),
            default => $collection->products->toBase(),
        };
    }
}

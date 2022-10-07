<?php

namespace Lunar\Actions\Collections;

use Illuminate\Support\Collection;

class SortProductsBySku
{
    /**
     * Execute the action.
     *
     * @param  \Illuminate\Support\Collection  $products
     * @param  string  $direction
     * @return \Illuminate\Support\Collection
     */
    public function execute(Collection $products, $direction = 'asc')
    {
        return $products->sort(function ($current, $next) use ($direction) {
            $currentVariant = $current->variants()->orderBy('sku', $direction)->first();
            $nextVariant = $next->variants()->orderBy('sku', $direction)->first();

            return $direction == 'asc' ?
                ($currentVariant->sku > $nextVariant->sku) :
                ($currentVariant->sku < $nextVariant->sku);
        });
    }
}

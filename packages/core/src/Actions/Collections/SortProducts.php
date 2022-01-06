<?php

namespace GetCandy\Actions\Collections;

use GetCandy\Models\Collection;
use GetCandy\Models\Currency;
use GetCandy\Models\Product;

class SortProducts
{
    /**
     * Execute the action.
     *
     * @param  Model  $owner
     * @param  \Illuminate\Support\Collection  $groups
     * @return void
     */
    public function execute(Collection $collection)
    {
        [$sort, $direction] = explode(':', $collection->sort);

        switch ($sort) {
            case 'min_price':
                $products = app(SortProductsByPrice::class)->execute(
                    $collection->products,
                    Currency::getDefault(),
                    $direction
                );
                break;
            case 'sku':
                $products = app(SortProductsBySku::class)->execute(
                    $collection->products,
                    $direction
                );
                break;
            default:
                $products = $collection->products;
                break;
        }

        return $products;
    }
}

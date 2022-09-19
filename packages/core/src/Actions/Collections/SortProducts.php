<?php

namespace Lunar\Actions\Collections;

use Lunar\Models\Collection;
use Lunar\Models\Currency;

class SortProducts
{
    /**
     * Execute the action.
     *
     * @param  \Lunar\Models\Collection  $collection
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

<?php

namespace Lunar\Actions\Collections;

use Lunar\Models\Collection;
use Lunar\Models\Contracts\Collection as CollectionContract;
use Lunar\Models\Currency;

class SortProducts
{
    /**
     * Execute the action.
     *
     * @return void
     */
    public function execute(CollectionContract $collection)
    {
        /** @var Collection $collection */
        [$sort, $direction] = explode(':', $collection->sort);

        switch ($sort) {
            case 'min_price':
                $products = app(SortProductsByPrice::class)->execute(
                    $collection->products,
                    Currency::modelClass()::getDefault(),
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

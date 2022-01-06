<?php

namespace GetCandy\Actions\Collections;

use GetCandy\Models\Currency;
use GetCandy\Models\Product;
use Illuminate\Support\Collection;

class SortProductsByPrice
{
    /**
     * Execute the action.
     *
     * @param Model                          $owner
     * @param \Illuminate\Support\Collection $groups
     *
     * @return void
     */
    public function execute(Collection $products, Currency $currency, $direction = 'asc')
    {
        // Load up our products and prices.
        $products = $products->load('variants.basePrices');

        return $products->sort(function ($current, $next) use ($currency, $direction) {
            $currentPrice = $this->getMinPrice($current, $currency);
            $nextPrice = $this->getMinPrice($next, $currency);

            return $direction == 'asc' ? ($currentPrice > $nextPrice) : ($currentPrice < $nextPrice);
        });
    }

    protected function getMinPrice(Product $product, Currency $currency)
    {
        return $product->variants->map(function ($variant) use ($currency) {
            // Get the prices for the currency
            return $variant->basePrices->filter(function ($price) use ($currency) {
                return $price->currency_id == $currency->id;
            })->min('price');
        })->min();
    }
}

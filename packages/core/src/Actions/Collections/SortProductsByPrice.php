<?php

namespace Lunar\Core\Actions\Collections;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Product;

class SortProductsByPrice
{
    /**
     * Execute the action.
     */
    public function execute(Collection $products, Currency $currency, $direction = 'asc')
    {
        /** @var Collection $products */
        // Load up our products and prices.
        $products = $products->load('variants.basePrices.currency');

        return $products->sort(function ($current, $next) use ($currency, $direction) {
            $currentPrice = $this->getMinPrice($current, $currency);
            $nextPrice = $this->getMinPrice($next, $currency);

            return $direction == 'asc' ? ($currentPrice > $nextPrice) : ($currentPrice < $nextPrice);
        });
    }

    protected function getMinPrice(Product $product, Currency $currency)
    {
        /** @var Product $product */
        /** @var Currency $currency */
        return $product->variants->map(function ($variant) use ($currency) {
            // Get the prices for the currency
            return $variant->basePrices->filter(function ($price) use ($currency) {
                return $price->currency_id == $currency->id;
            })->min('price');
        })->min();
    }
}

<?php

namespace GetCandy\Observers;

use GetCandy\Models\Price;
use GetCandy\Models\Product;

class PriceObserver
{
    /**
     * Handle the Price "created" event.
     *
     * @param  \GetCandy\Models\Price  $price
     * @return void
     */
    public function created(Price $price)
    {
        $this->denormaliseToProduct($price);
    }

    /**
     * Handle the Price "updated" event.
     *
     * @param  \GetCandy\Models\Price  $price
     * @return void
     */
    public function updated(Price $price)
    {
        $this->denormaliseToProduct($price);
    }

    /**
     * Handle the Currency "deleting" event.
     *
     * @param  \GetCandy\Models\Price  $price
     * @return void
     */
    public function deleting(Price $price)
    {
        $price->load('priceable', 'currency');

        if ($price->priceable && $price->priceable->product && $price->tier == 1) {
            $product = $price->priceable->product;

            $sorting = $product->sorting;

            if ($price->customer_group_id) {
                unset($sorting['price_customer_group_'.$price->customer_group_id.'_'.strtolower($price->currency->code)]);
            }

            $product->sorting = $sorting;
            $product->save();
        }
    }

    /**
     * Saves basic price information to related Product to simply ordering of Products.
     *
     * @param  \GetCandy\Models\Price  $price  The price that was just saved.
     * @return void
     */
    protected function denormaliseToProduct(Price $price): void
    {
        $price->load('priceable', 'currency');

        if ($price->priceable && $price->priceable->product && $price->tier == 1) {
            $product = $price->priceable->product;

            $sorting = $product->sorting;

            if ($price->customer_group_id) {
                $sorting['price_customer_group_'.$price->customer_group_id.'_'.strtolower($price->currency->code)] = $price->price->value;
            } else {
                $sorting['price_'.strtolower($price->currency->code)] = $price->price->value;

                if ($price->currency->default) {
                    $sorting['price_default'] = $price->price->value;
                }
            }

            $product->sorting = $sorting;
            $product->save();
        }
    }
}

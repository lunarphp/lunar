<?php

namespace Lunar\Hub\Http\Livewire\Traits;

use Illuminate\Support\Collection;
use Lunar\Models\Brand;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

trait SearchesProducts
{
    /**
     * Method to return computed brands.
     */
    public function getBrandsProperty(): Collection
    {
        return Brand::get();
    }

    /**
     * Method to return computed product types.
     */
    public function getProductTypesProperty(): Collection
    {
        return ProductType::get();
    }

    /**
     * Method to search products by a distinct column.
     *
     * @param  string  $column
     * @param  string  $term
     */
    protected function searchDistinct($column, $term = null): Collection
    {
        return Product::distinct($column)->where($column, 'LIKE', "%{$term}%")->pluck($column);
    }
}

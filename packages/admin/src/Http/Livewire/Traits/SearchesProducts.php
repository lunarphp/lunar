<?php

namespace GetCandy\Hub\Http\Livewire\Traits;

use GetCandy\Models\Product;
use GetCandy\Models\ProductType;
use Illuminate\Support\Collection;

trait SearchesProducts
{
    /**
     * List of filtered brands.
     *
     * @var array
     */
    public $brands = [];

    /**
     * Get brands by a given search term.
     *
     * @param string|null $term
     *
     * @return void
     */
    public function getBrands($term = null)
    {
        $this->brands = $term ? $this->searchDistinct('brand', $term) : [];
    }

    /**
     * Method to return computed product types.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProductTypesProperty(): Collection
    {
        return ProductType::get();
    }

    /**
     * Method to search products by a distinct column.
     *
     * @param string $column
     * @param string $term
     *
     * @return \Illuminate\Support\Collection
     */
    protected function searchDistinct($column, $term = null): Collection
    {
        return Product::distinct($column)->where($column, 'LIKE', "%{$term}%")->pluck($column);
    }
}

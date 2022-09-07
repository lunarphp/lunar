<?php

namespace GetCandy\Hub\Tables\Builders;

use GetCandy\Hub\Tables\TableBuilder;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;

class ProductVariantsTableBuilder extends TableBuilder
{
    protected Product $product;

    public function product($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Return the query data.
     *
     * @param  string|null  $searchTerm
     * @param  array  $filters
     * @param  string  $sortField
     * @param  string  $sortDir
     * @return LengthAwarePaginator
     */
    public function getData(): iterable
    {
        return ProductVariant::whereProductId($this->product->id)->paginate($this->perPage);
    }
}

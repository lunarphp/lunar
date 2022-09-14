<?php

namespace Lunar\Hub\Tables\Builders;

use Lunar\Hub\Tables\TableBuilder;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

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

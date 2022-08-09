<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products;

use GetCandy\Models\Product;

class ProductShow extends Resource
{
    public Product $product;

    protected static string $view = 'products.show';
}

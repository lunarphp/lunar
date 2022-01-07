<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products;

use GetCandy\Models\Product;
use Livewire\Component;

class ProductShow extends Component
{
    /**
     * The Product we are currently editing.
     *
     * @var \GetCandy\Models\Product
     */
    public Product $product;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.products.show')
            ->layout('adminhub::layouts.app', [
                'title' => 'Edit Product',
            ]);
    }
}

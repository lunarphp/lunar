<?php

namespace Lunar\Hub\Http\Livewire\Pages\Products;

use Livewire\Component;
use Lunar\Models\Product;

class ProductShow extends Component
{
    /**
     * The Product we are currently editing.
     *
     * @var \Lunar\Models\Product
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

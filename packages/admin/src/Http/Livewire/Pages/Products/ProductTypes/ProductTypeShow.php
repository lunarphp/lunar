<?php

namespace Lunar\Hub\Http\Livewire\Pages\Products\ProductTypes;

use Lunar\Models\ProductType;
use Livewire\Component;

class ProductTypeShow extends Component
{
    /**
     * The Product we are currently editing.
     *
     * @var \Lunar\Models\ProductType
     */
    public ProductType $productType;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.products.product-types.show')
            ->layout('adminhub::layouts.app', [
                'title' => 'Edit Product',
            ]);
    }
}

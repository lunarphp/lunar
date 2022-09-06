<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\ProductTypes;

use Livewire\Component;

class ProductTypesIndex extends Component
{

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.product-types.index')
        ->layout('adminhub::layouts.base');
    }
}

<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products;

use Livewire\Component;

class ProductsIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.index')
            ->layout('adminhub::layouts.base');
    }
}

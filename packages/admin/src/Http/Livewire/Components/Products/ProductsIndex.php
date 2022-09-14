<?php

namespace Lunar\Hub\Http\Livewire\Components\Products;

use GetCandy\Hub\Http\Livewire\Traits\Notifies;
use Livewire\Component;

class ProductsIndex extends Component
{
    use Notifies;

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

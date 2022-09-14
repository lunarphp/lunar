<?php

namespace Lunar\Hub\Http\Livewire\Pages\Products\ProductTypes;

use Livewire\Component;

class ProductTypeIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.products.product-types.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Product Types',
            ]);
    }
}

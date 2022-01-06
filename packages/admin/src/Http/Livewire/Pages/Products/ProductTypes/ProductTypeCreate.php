<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products\ProductTypes;

use GetCandy\Models\ProductType;
use Livewire\Component;

class ProductTypeCreate extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.products.product-types.create')
            ->layout('adminhub::layouts.app', [
                'title' => 'Create Product Type',
            ]);
    }
}

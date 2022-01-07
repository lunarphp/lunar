<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Products;

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
        return view('adminhub::livewire.pages.products.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Products',
            ]);
    }
}

<?php

namespace GetCandy\Hub\Http\Livewire\Pages\ProductTypes;

class ProductTypesIndex extends AbstractProductType
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.product-types.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Product Types',
            ]);
    }
}

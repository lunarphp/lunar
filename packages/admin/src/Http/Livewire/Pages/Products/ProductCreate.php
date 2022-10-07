<?php

namespace Lunar\Hub\Http\Livewire\Pages\Products;

use Livewire\Component;

class ProductCreate extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.products.create')
            ->layout('adminhub::layouts.app', [
                'title' => 'Create Product',
            ]);
    }
}

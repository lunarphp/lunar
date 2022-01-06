<?php

namespace GetCandy\Hub\Http\Livewire\Components\Products\Editing;

use Livewire\Component;

class CustomerGroups extends Component
{
    public $product;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.editing.customer-groups')
            ->layout('adminhub::layouts.base');
    }
}

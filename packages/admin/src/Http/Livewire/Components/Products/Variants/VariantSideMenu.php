<?php

namespace Lunar\Hub\Http\Livewire\Components\Products\Variants;

class VariantSideMenu extends VariantShow
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.products.variants.side-menu')
            ->layout('adminhub::layouts.app');
    }
}

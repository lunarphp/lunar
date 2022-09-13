<?php

namespace Lunar\Hub\Http\Livewire\Pages\Brands;

use Livewire\Component;

class BrandsIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.brands.index')
            ->layout('adminhub::layouts.app', [
                'title' => 'Brands',
            ]);
    }
}

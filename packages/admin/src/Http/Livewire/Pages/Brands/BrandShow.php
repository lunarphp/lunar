<?php

namespace Lunar\Hub\Http\Livewire\Pages\Brands;

use Livewire\Component;
use Lunar\Models\Brand;

class BrandShow extends Component
{
    /**
     * The instance of the brand we want to edit.
     */
    public Brand $brand;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.brands.show')
            ->layout('adminhub::layouts.app', [
                'title' => $this->brand->name,
            ]);
    }
}

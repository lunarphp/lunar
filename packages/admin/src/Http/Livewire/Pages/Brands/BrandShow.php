<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Brands;

use GetCandy\Models\Brand;
use Livewire\Component;

class BrandShow extends Component
{
    /**
     * The instance of the brand we want to edit.
     *
     * @var Brand
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
                'title' => 'Customers',
            ]);
    }
}

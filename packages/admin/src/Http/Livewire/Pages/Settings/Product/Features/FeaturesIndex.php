<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Product\Features;

use Livewire\Component;

class FeaturesIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.product.features.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

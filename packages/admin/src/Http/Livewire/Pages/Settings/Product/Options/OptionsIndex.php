<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Product\Options;

use Livewire\Component;

class OptionsIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.product.options.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

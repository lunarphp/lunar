<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Product\Options;

use Livewire\Component;
use Lunar\Models\ProductOption;

class OptionEdit extends Component
{
    /**
     * The option to edit.
     */
    public ProductOption $productOption;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.product.options.edit')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

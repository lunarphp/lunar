<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Taxes;

use Livewire\Component;

class TaxZonesIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.taxes.tax-zones.index')
            ->layout('adminhub::layouts.settings', [
                'title' => __('adminhub::settings.taxes.tax-zones.index.title'),
                'menu' => 'settings',
            ]);
    }
}

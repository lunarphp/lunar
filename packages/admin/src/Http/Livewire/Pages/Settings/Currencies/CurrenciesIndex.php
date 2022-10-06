<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Currencies;

use Livewire\Component;

class CurrenciesIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.currencies.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

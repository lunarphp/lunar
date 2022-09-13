<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Currencies;

use Lunar\Models\Currency;
use Livewire\Component;

class CurrencyShow extends Component
{
    /**
     * The instance of the channel we want to edit.
     *
     * @var \Lunar\Models\Currency
     */
    public Currency $currency;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.currencies.show')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

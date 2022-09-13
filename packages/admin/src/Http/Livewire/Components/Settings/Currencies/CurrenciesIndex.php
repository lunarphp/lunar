<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Currencies;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Currency;

class CurrenciesIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.currencies.index', [
            'currencies' => Currency::paginate(5),
        ])->layout('adminhub::layouts.base');
    }
}

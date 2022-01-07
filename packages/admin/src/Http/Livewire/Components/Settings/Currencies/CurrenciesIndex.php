<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Currencies;

use GetCandy\Models\Currency;
use Livewire\Component;
use Livewire\WithPagination;

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

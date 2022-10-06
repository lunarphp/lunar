<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Languages;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Language;

class LanguagesIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.languages.index')
            ->layout('adminhub::layouts.base');
    }
}

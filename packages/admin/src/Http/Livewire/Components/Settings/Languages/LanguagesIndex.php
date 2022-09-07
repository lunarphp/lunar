<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Languages;

use Livewire\Component;
use Livewire\WithPagination;

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

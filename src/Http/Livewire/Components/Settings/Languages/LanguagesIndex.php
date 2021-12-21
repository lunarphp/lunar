<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Languages;

use GetCandy\Models\Language;
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
        return view('adminhub::livewire.components.settings.languages.index', [
            'languages' => Language::paginate(5),
        ])->layout('adminhub::layouts.base');
    }
}

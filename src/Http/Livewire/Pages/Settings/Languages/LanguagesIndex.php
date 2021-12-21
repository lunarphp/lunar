<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Languages;

use Livewire\Component;

class LanguagesIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.languages.index')
            ->layout('adminhub::layouts.settings', [
                'title' => __('adminhub::settings.languages.index.title'),
            ]);
    }
}

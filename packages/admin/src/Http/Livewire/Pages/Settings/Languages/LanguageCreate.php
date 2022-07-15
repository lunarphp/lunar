<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Languages;

use Livewire\Component;

class LanguageCreate extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.languages.create')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

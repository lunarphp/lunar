<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Languages;

use GetCandy\Models\Language;
use Livewire\Component;

class LanguageShow extends Component
{
    /**
     * The instance of the channel we want to edit.
     *
     * @var Channel
     */
    public Language $language;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.languages.show')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

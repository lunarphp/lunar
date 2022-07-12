<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Tags;

use Livewire\Component;

class TagsIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.tags.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

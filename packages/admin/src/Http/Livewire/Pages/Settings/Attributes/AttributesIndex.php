<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Attributes;

use Livewire\Component;

class AttributesIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.attributes.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

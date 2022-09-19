<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Addons;

use Livewire\Component;

class AddonsIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.addons.index')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Addons;

use Livewire\Component;

class AddonShow extends Component
{
    public $addon;

    public function mount($addon)
    {
        $this->addon = $addon;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.addons.show')
            ->layout('adminhub::layouts.settings', [
                'title' => 'Addon',
            ]);
    }
}

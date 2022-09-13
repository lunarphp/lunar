<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Addons;

use Lunar\Addons\Manifest;
use Livewire\Component;

class AddonShow extends Component
{
    public $addon;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render(Manifest $manifest)
    {
        $addon = $manifest->addons()->first(fn ($addon) => $addon['marketplaceId'] === $this->addon);

        return view('adminhub::livewire.components.settings.addons.show', [
            'details' => $addon,
        ])->layout('adminhub::layouts.base');
    }
}

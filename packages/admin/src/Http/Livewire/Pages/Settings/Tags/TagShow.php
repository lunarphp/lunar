<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Tags;

use Lunar\Models\Tag;
use Livewire\Component;

class TagShow extends Component
{
    public Tag $tag;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.tags.show')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

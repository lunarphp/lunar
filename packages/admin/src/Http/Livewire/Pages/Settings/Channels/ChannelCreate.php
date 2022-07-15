<?php

namespace GetCandy\Hub\Http\Livewire\Pages\Settings\Channels;

use Livewire\Component;

class ChannelCreate extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.channels.create')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

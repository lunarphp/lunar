<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Channels;

use Livewire\Component;
use Lunar\Models\Channel;

class ChannelShow extends Component
{
    /**
     * The instance of the channel we want to edit.
     */
    public Channel $channel;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.pages.settings.channels.show')
            ->layout('adminhub::layouts.settings', [
                'menu' => 'settings',
            ]);
    }
}

<?php

namespace Lunar\Hub\Http\Livewire\Pages\Settings\Channels;

use Lunar\Models\Channel;
use Livewire\Component;

class ChannelShow extends Component
{
    /**
     * The instance of the channel we want to edit.
     *
     * @var Channel
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

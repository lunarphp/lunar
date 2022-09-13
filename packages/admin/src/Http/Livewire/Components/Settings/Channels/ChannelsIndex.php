<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Channels;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Channel;

class ChannelsIndex extends Component
{
    use WithPagination;

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.channels.index', [
            'channels' => Channel::paginate(5),
        ])->layout('adminhub::layouts.base');
    }
}

<?php

namespace GetCandy\Hub\Http\Livewire\Components\Settings\Channels;

use GetCandy\Models\Channel;
use Livewire\Component;
use Livewire\WithPagination;

class ChannelsIndex extends Component
{
    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.channels.index')->layout('adminhub::layouts.base');
    }
}

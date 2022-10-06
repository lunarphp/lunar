<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Channels;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Channel;

class ChannelCreate extends Component
{
    use Notifies;

    /**
     * A new instance of the channel model.
     *
     * @var Channel
     */
    public Channel $channel;

    /**
     * Called when we mount the component.
     *
     * @return void
     */
    public function mount()
    {
        $this->channel = new Channel();
        $this->channel->default = false;
    }

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $table = $this->channel->getTable();

        return [
            'channel.name' => 'required|string|max:255',
            'channel.handle' => "required|string|unique:$table,handle|max:255",
            'channel.url' => 'nullable|url|max:255',
            'channel.default' => 'boolean',
        ];
    }

    /**
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function create()
    {
        $this->validate();

        $this->channel->save();

        $this->notify(
            'Channel successfully created.',
            'hub.channels.index'
        );
    }

    /**
     * Toggles the default attribute of the model.
     *
     * @return void
     */
    public function toggleDefault()
    {
        $this->channel->default = ! $this->channel->default;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.channels.create')
            ->layout('adminhub::layouts.base');
    }
}

<?php

namespace Lunar\Hub\Http\Livewire\Components\Settings\Channels;

use Livewire\Component;
use Lunar\Hub\Http\Livewire\Traits\Notifies;
use Lunar\Models\Channel;

class ChannelShow extends Component
{
    use Notifies;

    /**
     * The current channel we're showing.
     */
    public Channel $channel;

    /**
     * Defines the confirmation text when deleting a channel.
     *
     * @var string|null
     */
    public $deleteConfirm = null;

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'channel.name' => 'required|string|max:255',
            'channel.handle' => 'required|string|max:255|unique:'.Channel::class.',handle,'.$this->channel->id,
            'channel.url' => 'nullable|url|max:255',
            'channel.default' => 'nullable',
        ];
    }

    /**
     * Validates the LiveWire request, updates the model and dispatches and event.
     *
     * @return void
     */
    public function update()
    {
        $this->validate();

        $this->channel->save();

        $this->notify(
            'Channel successfully updated.',
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
     * Soft deletes a channel.
     *
     * @return void
     */
    public function delete()
    {
        if (! $this->canDelete) {
            return;
        }

        $this->channel->delete();

        $this->notify(
            'Channel successfully deleted.',
            'hub.channels.index'
        );
    }

    /**
     * Returns whether we have met the criteria to allow deletion.
     *
     * @return bool
     */
    public function getCanDeleteProperty()
    {
        return $this->deleteConfirm === $this->channel->name;
    }

    /**
     * Render the livewire component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('adminhub::livewire.components.settings.channels.show')
            ->layout('adminhub::layouts.base');
    }
}

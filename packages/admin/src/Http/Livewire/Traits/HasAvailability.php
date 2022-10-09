<?php

namespace Lunar\Hub\Http\Livewire\Traits;

use Lunar\Models\Channel;
use Lunar\Models\CustomerGroup;

trait HasAvailability
{
    /**
     * Computed method to get all available channels.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getChannelsProperty()
    {
        return Channel::get();
    }

    /**
     * Computed method to get all available customer groups.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerGroupsProperty()
    {
        return CustomerGroup::get();
    }

    /**
     * Computed method to determine if the model has any channel availability.
     *
     * @return bool
     */
    public function getHasChannelAvailabilityProperty()
    {
        return (bool) collect($this->availability['channels'])->filter(function ($channel) {
            return (bool) $channel['enabled'];
        })->count();
    }

    /**
     * Method to sync availability with the model.
     *
     * @return void
     */
    abstract protected function syncAvailability();
}

<?php

namespace Lunar\Observers;

use Lunar\Models\Channel;
use Lunar\Models\Contracts\Channel as ChannelContract;

class ChannelObserver
{
    /**
     * Handle the User "created" event.
     *
     * @return void
     */
    public function created(ChannelContract $channel)
    {
        $this->ensureOnlyOneDefault($channel);
    }

    /**
     * Handle the User "updated" event.
     *
     * @return void
     */
    public function updated(ChannelContract $channel)
    {
        $this->ensureOnlyOneDefault($channel);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @return void
     */
    public function deleted(ChannelContract $channel)
    {
        //
    }

    /**
     * Handle the User "forceDeleted" event.
     *
     * @return void
     */
    public function forceDeleted(ChannelContract $channel)
    {
        //
    }

    /**
     * Ensures that only one default channel exists.
     *
     * @param  Channel  $savedChannel  The channel that was just saved.
     */
    protected function ensureOnlyOneDefault(ChannelContract $savedChannel): void
    {
        // Wrap here so we avoid a query if it's not been set to default.
        if ($savedChannel->default) {
            $channel = Channel::whereDefault(true)->where('id', '!=', $savedChannel->id)->first();

            if ($channel) {
                $channel->default = false;
                $channel->saveQuietly();
            }
        }
    }
}

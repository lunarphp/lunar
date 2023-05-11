<?php

namespace Lunar\Observers;

use Lunar\Models\Channel;

class ChannelObserver
{
    /**
     * Handle the User "created" event.
     *
     * @return void
     */
    public function created(Channel $channel)
    {
        $this->ensureOnlyOneDefault($channel);
    }

    /**
     * Handle the User "updated" event.
     *
     * @return void
     */
    public function updated(Channel $channel)
    {
        $this->ensureOnlyOneDefault($channel);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @return void
     */
    public function deleted(Channel $channel)
    {
        //
    }

    /**
     * Handle the User "forceDeleted" event.
     *
     * @return void
     */
    public function forceDeleted(Channel $channel)
    {
        //
    }

    /**
     * Ensures that only one default channel exists.
     *
     * @param  Channel  $savedChannel  The channel that was just saved.
     */
    protected function ensureOnlyOneDefault(Channel $savedChannel): void
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

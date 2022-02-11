<?php

namespace GetCandy\Observers;

use GetCandy\Models\Channel;

class ChannelObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \GetCandy\Models\Channel  $channel
     * @return void
     */
    public function created(Channel $channel)
    {
        $this->ensureOnlyOneDefault($channel);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \GetCandy\Models\Channel  $channel
     * @return void
     */
    public function updated(Channel $channel)
    {
        $this->ensureOnlyOneDefault($channel);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \GetCandy\Models\Channel  $channel
     * @return void
     */
    public function deleted(Channel $channel)
    {
        //
    }

    /**
     * Handle the User "forceDeleted" event.
     *
     * @param  \GetCandy\Models\Channel  $channel
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
     * @return void
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

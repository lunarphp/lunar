<?php

namespace Lunar\Core\Actions\Channels;

use Lunar\Core\Contracts\Actions\Channels\DeletesChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;

/**
 * Delete a channel. Channels with order history are kept — mark them
 * Inactive in the admin instead — so historical orders keep their context.
 */
class DeleteChannel implements DeletesChannel
{
    public function execute(Channel $channel): void
    {
        if ($channel->hasOrderHistory()) {
            throw new ChannelActionException('Cannot delete a channel with order history.');
        }

        $channel->delete();
    }
}

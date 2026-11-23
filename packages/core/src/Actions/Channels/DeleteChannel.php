<?php

namespace Lunar\Core\Actions\Channels;

use Lunar\Core\Contracts\Actions\Channels\DeletesChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Region;

/**
 * Delete a channel. Channels with order history are kept — mark them
 * Inactive in the admin instead — so historical orders keep their context.
 * The default channel is also kept: make another channel the default first.
 * Channels that a region or a cart still points at are kept too, since both
 * require a channel.
 *
 * Availability rows in `channelables` are configuration rather than history:
 * products, collections and discounts get a row for every channel when they
 * are created, so they are removed together with the channel.
 */
class DeleteChannel implements DeletesChannel
{
    public function execute(Channel $channel): void
    {
        if ($channel->default) {
            throw new ChannelActionException('Cannot delete the default channel. Make another channel the default first.');
        }

        if ($channel->hasOrderHistory()) {
            throw new ChannelActionException('Cannot delete a channel with order history.');
        }

        if (Region::query()->where('channel_id', $channel->id)->exists()) {
            throw new ChannelActionException('Cannot delete a channel that a region uses.');
        }

        if (Cart::query()->where('channel_id', $channel->id)->exists()) {
            throw new ChannelActionException('Cannot delete a channel that carts use.');
        }

        DB::transaction(function () use ($channel): void {
            DB::table(config('lunar.database.table_prefix').'channelables')
                ->where('channel_id', $channel->id)
                ->delete();

            $channel->delete();
        });
    }
}

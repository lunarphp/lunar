<?php

namespace Lunar\Core\Actions\Channels;

use Lunar\Core\Contracts\Actions\Channels\UpdatesChannel;
use Lunar\Core\Exceptions\ChannelActionException;
use Lunar\Core\Models\Channel;

/**
 * Update a channel, ensuring at most one channel is ever marked default.
 * The default flag moves by promoting another channel, never by unsetting —
 * so a store with channels always has a default. The handle is kept unless
 * a new one is passed; it is never derived from a new name.
 */
class UpdateChannel implements UpdatesChannel
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Channel $channel, array $attributes): Channel
    {
        if ($channel->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new ChannelActionException('Cannot unset the default channel. Make another channel the default instead.');
        }

        // Storefronts look channels up by handle, so renaming must not change
        // it. Only an explicitly passed handle replaces the existing one.
        $attributes['handle'] ??= $channel->handle;

        if ($attributes['default'] ?? false) {
            Channel::query()->where('default', true)->where('id', '!=', $channel->id)->update(['default' => false]);
        }

        $channel->update($attributes);

        return $channel;
    }
}

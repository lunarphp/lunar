<?php

namespace Lunar\Core\Actions\Channels;

use Lunar\Core\Contracts\Actions\Channels\UpdatesChannel;
use Lunar\Core\Models\Channel;

/**
 * Update a channel, ensuring at most one channel is ever marked default.
 */
class UpdateChannel implements UpdatesChannel
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Channel $channel, array $attributes): Channel
    {
        $attributes['handle'] ??= $attributes['name'] ?? $channel->handle;

        if ($attributes['default'] ?? false) {
            Channel::query()->where('default', true)->where('id', '!=', $channel->id)->update(['default' => false]);
        }

        $channel->update($attributes);

        return $channel;
    }
}

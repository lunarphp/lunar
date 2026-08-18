<?php

namespace Lunar\Core\Actions\Channels;

use Lunar\Core\Contracts\Actions\Channels\CreatesChannel;
use Lunar\Core\Models\Channel;

/**
 * Create a channel, ensuring at most one channel is ever marked default.
 */
class CreateChannel implements CreatesChannel
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Channel
    {
        $attributes['handle'] ??= $attributes['name'] ?? null;

        if ($attributes['default'] ?? false) {
            Channel::query()->where('default', true)->update(['default' => false]);
        }

        return Channel::create($attributes);
    }
}

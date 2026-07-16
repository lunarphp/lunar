<?php

namespace Lunar\Core\Contracts\Actions\Channels;

use Lunar\Core\Models\Channel;

interface UpdatesChannel
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Channel $channel, array $attributes): Channel;
}

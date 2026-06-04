<?php

namespace Lunar\Core\States\Channel;

class Active extends ChannelState
{
    public static string $name = 'active';

    public function label(): string
    {
        return __('lunar::states.channel.active');
    }
}

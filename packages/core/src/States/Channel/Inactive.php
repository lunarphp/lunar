<?php

namespace Lunar\Core\States\Channel;

class Inactive extends ChannelState
{
    public static string $name = 'inactive';

    public function label(): string
    {
        return __('lunar::states.channel.inactive');
    }
}

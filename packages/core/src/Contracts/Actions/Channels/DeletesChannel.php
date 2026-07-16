<?php

namespace Lunar\Core\Contracts\Actions\Channels;

use Lunar\Core\Models\Channel;

interface DeletesChannel
{
    public function execute(Channel $channel): void;
}

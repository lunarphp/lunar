<?php

namespace Lunar\Core\Contracts\Actions\Channels;

use Lunar\Core\Models\Channel;

interface CreatesChannel
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Channel;
}

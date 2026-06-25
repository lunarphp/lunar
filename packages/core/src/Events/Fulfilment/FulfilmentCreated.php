<?php

namespace Lunar\Core\Events\Fulfilment;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Fulfilment;

class FulfilmentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Fulfilment $fulfilment,
    ) {}
}

<?php

namespace Lunar\Core\Events\Fulfilment;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\States\Fulfilment\FulfilmentState;

class FulfilmentStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Fulfilment $fulfilment,
        public ?FulfilmentState $previousStatus,
        public FulfilmentState $newStatus,
        public bool $notify = true,
    ) {}
}

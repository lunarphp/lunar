<?php

namespace Lunar\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Base\DataTransferObjects\RefundRequest;

class RefundRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RefundRequest $refundRequest,
    ) {
        //
    }
}

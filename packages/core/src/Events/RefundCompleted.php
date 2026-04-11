<?php

namespace Lunar\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Base\DataTransferObjects\RefundRequest;

class RefundCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RefundRequest $refundRequest,
        public PaymentRefund $paymentRefund,
    ) {
        //
    }
}

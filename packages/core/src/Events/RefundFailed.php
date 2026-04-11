<?php

namespace Lunar\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Base\DataTransferObjects\RefundRequest;

class RefundFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public RefundRequest $refundRequest,
        public ?PaymentRefund $paymentRefund = null,
        public ?string $message = null,
        public array $meta = [],
    ) {
        //
    }
}

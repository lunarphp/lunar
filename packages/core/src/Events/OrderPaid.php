<?php

namespace Lunar\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public Order $order,
        public PaymentAuthorize $paymentAuthorize,
        public ?Transaction $transaction = null,
        public array $meta = [],
    ) {
        //
    }
}

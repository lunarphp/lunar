<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Models\Transaction;

class PaymentRefund
{
    /**
     * @param  ?Transaction  $transaction  The refund transaction the driver created, when it creates one. Nullable — not every driver records the money movement as a transaction row (e.g. the offline driver); callers that need to attribute a refund to line-level bookkeeping should treat a null transaction as "recorded, but not attributable".
     * @param  ?string  $reference  The gateway's own reference for the refund, so it can be traced in the gateway whether or not a transaction row was written.
     * @param  bool  $pending  True when the gateway accepted the refund but has not settled it.
     */
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
        public ?Transaction $transaction = null,
        public ?string $reference = null,
        public bool $pending = false,
    ) {
        //
    }
}

<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Models\Transaction;

class PaymentRefund
{
    /**
     * @param  ?Transaction  $transaction  The refund transaction the driver created, when it creates one. Nullable — not every driver records the money movement as a transaction row (e.g. the offline driver); callers that need to attribute a refund to line-level bookkeeping should treat a null transaction as "recorded, but not attributable".
     */
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
        public ?Transaction $transaction = null,
    ) {
        //
    }
}

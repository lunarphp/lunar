<?php

namespace Lunar\Base\DataTransferObjects;

class PaymentRefund
{
    /**
     * @param  array<string, mixed>|null  $meta
     * @param  array<int, array<string, mixed>>|null  $lineAllocations
     */
    public function __construct(
        public bool $success = false,
        public ?string $message = null,
        public ?int $refundTransactionId = null,
        public ?string $reference = null,
        public ?string $status = null,
        public ?array $meta = null,
        public ?array $lineAllocations = null,
    ) {
        //
    }
}

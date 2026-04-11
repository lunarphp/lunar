<?php

namespace Lunar\Base\DataTransferObjects;

use Lunar\Models\Transaction;

class RefundRequest
{
    /**
     * @param  array<string, mixed>  $meta
     * @param  array<int, array<string, mixed>>|null  $lineAllocations
     */
    public function __construct(
        public Transaction $transaction,
        public int $amount,
        public ?string $notes = null,
        public ?int $actorId = null,
        public array $meta = [],
        public ?array $lineAllocations = null,
    ) {
        //
    }
}

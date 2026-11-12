<?php

namespace Lunar\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Core\Models\Discount;

/**
 * Generates a batch too large to make in a request. A retry finishes the same
 * batch, because the batch ULID travels with the job.
 */
class GenerateCouponsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Discount $discount,
        public CouponBatch $batch,
    ) {}

    public function handle(): void
    {
        $this->discount->generateCoupons($this->batch);
    }
}

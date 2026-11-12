<?php

namespace Lunar\Panel\Support;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\Bus;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Core\Jobs\GenerateCouponsJob;
use Lunar\Core\Models\Discount;

/**
 * The queued generation runs of a discount, so the edit page can show a batch
 * as generating until its codes are all there. Progress is read from the batch's
 * rows; the cache only remembers how many were asked for and whether the job failed.
 */
class CouponGenerations
{
    protected const TTL_SECONDS = 86400;

    public function __construct(
        protected Cache $cache,
    ) {}

    public function queue(Discount $discount, CouponBatch $batch): void
    {
        $this->remember($discount->id, $batch->id, ['quantity' => $batch->quantity, 'failed' => false]);

        $discountId = $discount->id;
        $batchId = $batch->id;

        Bus::chain([new GenerateCouponsJob($discount, $batch)])
            // Static, so the queued callback doesn't serialize this instance and its cache store.
            ->catch(static fn () => app(self::class)->markFailed($discountId, $batchId))
            ->dispatch();
    }

    public function markFailed(int $discountId, string $batchId): void
    {
        $runs = $this->runs($discountId);

        if (isset($runs[$batchId])) {
            $this->remember($discountId, $batchId, ['failed' => true] + $runs[$batchId]);
        }
    }

    /**
     * The runs that have not finished: still generating, or failed part way.
     *
     * @return list<array{batch: string, quantity: int, generated: int, failed: bool}>
     */
    public function unfinished(Discount $discount): array
    {
        $runs = $this->runs($discount->id);

        if ($runs === []) {
            return [];
        }

        $generated = $discount->coupons()
            ->whereIn('batch', array_keys($runs))
            ->groupBy('batch')
            ->selectRaw('batch, count(*) as aggregate')
            ->pluck('aggregate', 'batch');

        $unfinished = [];

        foreach ($runs as $batchId => $run) {
            $count = (int) ($generated[$batchId] ?? 0);

            if ($count < $run['quantity']) {
                $unfinished[] = ['batch' => $batchId, 'quantity' => $run['quantity'], 'generated' => $count, 'failed' => $run['failed']];
            }
        }

        return $unfinished;
    }

    /**
     * @return array<string, array{quantity: int, failed: bool}>
     */
    protected function runs(int $discountId): array
    {
        return $this->cache->get($this->key($discountId), []);
    }

    /**
     * @param  array{quantity: int, failed: bool}  $run
     */
    protected function remember(int $discountId, string $batchId, array $run): void
    {
        $runs = $this->runs($discountId);
        $runs[$batchId] = $run;

        $this->cache->put($this->key($discountId), $runs, static::TTL_SECONDS);
    }

    protected function key(int $discountId): string
    {
        return "lunar.panel.discounts.{$discountId}.coupon-generations";
    }
}

<?php

namespace Lunar\Core\Actions\Discounts;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Core\Contracts\Actions\Discounts\GeneratesCoupons;
use Lunar\Core\Contracts\CouponCodeGenerator;
use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use RuntimeException;

/**
 * Codes are inserted in chunks with insertOrIgnore, and each pass tops up
 * what the batch still lacks, so a code that collides with an existing one is
 * replaced rather than lost. Progress is read from the batch's own rows, so
 * running the same batch again finishes it instead of duplicating it.
 */
class GenerateCoupons implements GeneratesCoupons
{
    protected const CHUNK = 1000;

    /**
     * Consecutive passes that insert nothing before giving up. Only a code
     * space that is nearly used up gets here, e.g. a very short length.
     */
    protected const MAX_EMPTY_PASSES = 10;

    public function __construct(
        protected CouponCodeGenerator $generator,
    ) {}

    public function execute(Discount $discount, CouponBatch $batch): string
    {
        if ($batch->isForEmails()) {
            $this->generateForEmails($discount, $batch);
        } else {
            $this->generateQuantity($discount, $batch);
        }

        return $batch->id;
    }

    protected function generateQuantity(Discount $discount, CouponBatch $batch): void
    {
        $remaining = $batch->quantity - $this->batchCoupons($batch)->count();
        $emptyPasses = 0;

        while ($remaining > 0) {
            $inserted = $this->insert($discount, $batch, array_fill(0, min($remaining, static::CHUNK), null));

            $remaining -= $inserted;
            $emptyPasses = $this->countEmptyPass($inserted, $emptyPasses);
        }
    }

    protected function generateForEmails(Discount $discount, CouponBatch $batch): void
    {
        foreach (array_chunk($batch->emails, static::CHUNK) as $emails) {
            $emptyPasses = 0;

            while ($missing = $this->emailsWithoutCode($batch, $emails)) {
                $inserted = $this->insert($discount, $batch, $missing);

                $emptyPasses = $this->countEmptyPass($inserted, $emptyPasses);
            }
        }
    }

    /**
     * @param  list<string>  $emails
     * @return list<string>
     */
    protected function emailsWithoutCode(CouponBatch $batch, array $emails): array
    {
        $issued = $this->batchCoupons($batch)->whereIn('email', $emails)->pluck('email')->all();

        return array_values(array_diff($emails, $issued));
    }

    /**
     * @param  array<int, ?string>  $emails  One entry per code; null for an anonymous code.
     * @return int The number of codes inserted; the rest collided.
     */
    protected function insert(Discount $discount, CouponBatch $batch, array $emails): int
    {
        $now = now();

        $rows = array_map(fn (?string $email) => [
            'discount_id' => $discount->id,
            'code' => $this->generator->generate($batch),
            'uses' => 0,
            'max_uses' => $batch->maxUses,
            'email' => $email,
            'batch' => $batch->id,
            'created_at' => $now,
            'updated_at' => $now,
        ], $emails);

        return Coupon::query()->insertOrIgnore($rows);
    }

    protected function countEmptyPass(int $inserted, int $emptyPasses): int
    {
        if ($inserted > 0) {
            return 0;
        }

        if (++$emptyPasses >= static::MAX_EMPTY_PASSES) {
            throw new RuntimeException('Could not generate unique coupon codes. Use a longer code length.');
        }

        return $emptyPasses;
    }

    protected function batchCoupons(CouponBatch $batch): Builder
    {
        return Coupon::query()->where('batch', $batch->id);
    }
}

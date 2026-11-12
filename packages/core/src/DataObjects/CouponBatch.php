<?php

namespace Lunar\Core\DataObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Lunar\Core\Actions\Discounts\ResolveCustomerEmail;

/**
 * One run of generated codes: a number of anonymous codes, or one code per
 * email address. The batch ULID is fixed when the batch is described, so a
 * queued run that is retried tops up the same batch rather than starting another.
 */
final class CouponBatch
{
    public readonly string $id;

    public readonly string $prefix;

    /**
     * @param  list<string>  $emails
     */
    private function __construct(
        public readonly int $quantity,
        public readonly array $emails,
        public readonly int $length,
        string $prefix,
        public readonly ?int $maxUses,
    ) {
        if ($quantity < 1) {
            throw new InvalidArgumentException('A coupon batch needs at least one code.');
        }

        if ($length < 1) {
            throw new InvalidArgumentException('A coupon code needs at least one generated character.');
        }

        $this->id = (string) Str::ulid();
        // Rows are bulk inserted, which skips the model's uppercase cast.
        $this->prefix = Str::upper(trim($prefix));
    }

    /**
     * Anonymous codes. `length` counts the generated characters after the prefix.
     */
    public static function ofQuantity(int $quantity, int $length = 10, string $prefix = '', ?int $maxUses = 1): self
    {
        return new self($quantity, [], $length, $prefix, $maxUses);
    }

    /**
     * One code per distinct address, normalized the way per-customer limits
     * compare them. Blank entries are dropped.
     *
     * @param  iterable<string|null>  $emails
     */
    public static function forEmails(iterable $emails, int $length = 10, string $prefix = '', ?int $maxUses = 1): self
    {
        $distinct = [];

        foreach ($emails as $email) {
            $email = ResolveCustomerEmail::normalize($email);

            if ($email !== null) {
                $distinct[$email] = true;
            }
        }

        $distinct = array_map('strval', array_keys($distinct));

        return new self(count($distinct), $distinct, $length, $prefix, $maxUses);
    }

    public function isForEmails(): bool
    {
        return $this->emails !== [];
    }
}

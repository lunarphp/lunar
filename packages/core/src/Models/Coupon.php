<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Casts\CouponString;
use Lunar\Core\Database\Factories\CouponFactory;

/**
 * A code that redeems a coupon discount.
 *
 * @property int $id
 * @property int $discount_id
 * @property string $code
 * @property int $uses
 * @property ?int $max_uses
 * @property ?string $email
 * @property ?string $batch
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Coupon extends Base
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'code' => CouponString::class,
        'uses' => 'integer',
        'max_uses' => 'integer',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CouponFactory::new();
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query->where(function ($subQuery) {
            $subQuery->whereNull('max_uses')
                ->orWhereColumn('uses', '<', 'max_uses');
        });
    }

    public function isUsable(): bool
    {
        return $this->max_uses === null || $this->uses < $this->max_uses;
    }
}

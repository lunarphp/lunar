<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasChannels;
use Lunar\Base\Traits\HasCustomerGroups;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Database\Factories\DiscountFactory;
use Lunar\DiscountTypes\AbstractDiscountType;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property ?string $coupon
 * @property string $type
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property int $uses
 * @property ?int $max_uses
 * @property int $priority
 * @property bool $stop
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Discount extends BaseModel implements Contracts\Discount
{
    use HasChannels,
        HasCustomerGroups,
        HasFactory,
        HasTranslations;

    protected $guarded = [];

    const ACTIVE = 'active';

    const PENDING = 'pending';

    const EXPIRED = 'expired';

    const SCHEDULED = 'scheduled';

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return DiscountFactory::new();
    }

    public function getStatusAttribute(): string
    {
        $active = $this->starts_at?->isPast() && ! $this->ends_at?->isPast();
        $expired = $this->ends_at?->isPast();
        $future = $this->starts_at?->isFuture();

        if ($expired) {
            return static::EXPIRED;
        }

        if ($future) {
            return static::SCHEDULED;
        }

        return $active ? static::ACTIVE : static::PENDING;
    }

    public function users(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            config('auth.providers.users.model'),
            "{$prefix}discount_user"
        )->withTimestamps();
    }

    public function purchasables(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::modelClass());
    }

    public function purchasableConditions(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::modelClass())->whereType('condition');
    }

    public function purchasableExclusions(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::modelClass())->whereType('exclusion');
    }

    public function purchasableLimitations(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::modelClass())->whereType('limitation');
    }

    public function purchasableRewards(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::modelClass())->whereType('reward');
    }

    public function getType(): AbstractDiscountType
    {
        return app($this->type)->with($this);
    }

    public function collections(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Collection::modelClass(),
            "{$prefix}collection_discount"
        )->withPivot(['type'])->withTimestamps();
    }

    public function customers(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Customer::modelClass(),
            "{$prefix}customer_discount"
        )->withTimestamps();
    }

    public function customerGroups(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::modelClass(),
            "{$prefix}customer_group_discount"
        )->withPivot([
            'visible',
            'enabled',
            'starts_at',
            'ends_at',
        ])->withTimestamps();
    }

    public function brands(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Brand::modelClass(),
            "{$prefix}brand_discount"
        )->withPivot(['type'])->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    public function scopeProducts(Builder $query, iterable $productIds = [], array|string $types = []): Builder
    {
        if (is_array($productIds)) {
            $productIds = collect($productIds);
        }

        $types = Arr::wrap($types);

        return $query->where(
            fn ($subQuery) => $subQuery->whereDoesntHave('purchasables', fn ($query) => $query->when($types, fn ($query) => $query->whereIn('type', $types)))
                ->orWhereHas('purchasables',
                    fn ($relation) => $relation->whereIn('purchasable_id', $productIds)
                        ->wherePurchasableType(Product::morphName())
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn('type', $types)
                        )
                )
        );
    }

    public function scopeProductVariants(Builder $query, iterable $variantIds = [], array|string $types = []): Builder
    {
        if (is_array($variantIds)) {
            $variantIds = collect($variantIds);
        }

        $types = Arr::wrap($types);

        return $query->where(
            fn ($subQuery) => $subQuery->whereDoesntHave('purchasables', fn ($query) => $query->when($types, fn ($query) => $query->whereIn('type', $types)))
                ->orWhereHas('purchasables',
                    fn ($relation) => $relation->whereIn('purchasable_id', $variantIds)
                        ->wherePurchasableType(ProductVariant::morphName())
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn('type', $types)
                        )
                )
        );
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query->where(function ($subQuery) {
            $subQuery->whereRaw('uses < max_uses')
                ->orWhereNull('max_uses');
        });
    }
}

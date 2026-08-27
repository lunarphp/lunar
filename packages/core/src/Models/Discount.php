<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lunar\Core\Casts\CouponString;
use Lunar\Core\Database\Factories\DiscountFactory;
use Lunar\Core\DiscountTypes\AbstractDiscountType;
use Lunar\Core\Models\Concerns\HasChannels;
use Lunar\Core\Models\Concerns\HasCustomerGroups;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property string $handle
 * @property ?string $coupon
 * @property string $type
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property int $uses
 * @property ?int $max_uses
 * @property int $priority
 * @property bool $stop
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Discount extends Base
{
    use HasChannels,
        HasCustomerGroups,
        HasFactory,
        HasPublicId,
        HasTranslations,
        LogsActivity;

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
        'coupon' => CouponString::class,
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

    public function discountables(): HasMany
    {
        return $this->hasMany(Discountable::class);
    }

    public function discountableConditions(): HasMany
    {
        return $this->hasMany(Discountable::class)->whereType('condition');
    }

    public function discountableExclusions(): HasMany
    {
        return $this->hasMany(Discountable::class)->whereType('exclusion');
    }

    public function discountableLimitations(): HasMany
    {
        return $this->hasMany(Discountable::class)->whereType('limitation');
    }

    public function discountableRewards(): HasMany
    {
        return $this->hasMany(Discountable::class)->whereType('reward');
    }

    public function getType(): AbstractDiscountType
    {
        return app($this->type)->with($this);
    }

    public function collections(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Collection::class,
            "{$prefix}collection_discount"
        )->withPivot(['type'])->withTimestamps();
    }

    public function customers(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Customer::class,
            "{$prefix}customer_discount"
        )->withTimestamps();
    }

    public function customerGroups(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::class,
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
            Brand::class,
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

    public function scopeCollections(Builder $query, iterable $collectionIds = [], array|string $types = []): Builder
    {
        if (is_array($collectionIds)) {
            $collectionIds = collect($collectionIds);
        }

        $types = Arr::wrap($types);
        $prefix = config('lunar.database.table_prefix');

        return $query->where(
            fn ($subQuery) => $subQuery->whereDoesntHave('collections', fn ($query) => $query->when($types, fn ($query) => $query->whereIn("{$prefix}collection_discount.type", $types)))
                ->orWhereHas('collections',
                    fn ($relation) => $relation->whereIn('collection_id', $collectionIds)
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn("{$prefix}collection_discount.type", $types)
                        )
                )
        );
    }

    public function scopeBrands(Builder $query, iterable $brandIds = [], array|string $types = []): Builder
    {
        if (is_array($brandIds)) {
            $brandIds = collect($brandIds);
        }

        $types = Arr::wrap($types);
        $prefix = config('lunar.database.table_prefix');

        return $query->where(
            fn ($subQuery) => $subQuery->whereDoesntHave('brands', fn ($query) => $query->when($types, fn ($query) => $query->whereIn("{$prefix}brand_discount.type", $types)))
                ->orWhereHas('brands',
                    fn ($relation) => $relation->whereIn('brand_id', $brandIds)
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn("{$prefix}brand_discount.type", $types)
                        )
                )
        );
    }

    public function scopeProducts(Builder $query, iterable $productIds = [], array|string $types = []): Builder
    {
        if (is_array($productIds)) {
            $productIds = collect($productIds);
        }

        $types = Arr::wrap($types);
        $prefix = config('lunar.database.table_prefix');

        return $query->where(
            fn ($subQuery) => $subQuery->whereDoesntHave('discountables', fn ($query) => $query->whereDiscountableType(Product::morphName())->when($types, fn ($query) => $query->whereIn("{$prefix}discountables.type", $types)))
                ->orWhereHas('discountables',
                    fn ($relation) => $relation->whereIn('discountable_id', $productIds)
                        ->whereDiscountableType(Product::morphName())
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn("{$prefix}discountables.type", $types)
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
        $prefix = config('lunar.database.table_prefix');

        return $query->where(
            fn ($subQuery) => $subQuery->whereDoesntHave('discountables', fn ($query) => $query->whereDiscountableType(ProductVariant::morphName())->when($types, fn ($query) => $query->whereIn("{$prefix}discountables.type", $types)))
                ->orWhereHas('discountables',
                    fn ($relation) => $relation->whereIn('discountable_id', $variantIds)
                        ->whereDiscountableType(ProductVariant::morphName())
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn("{$prefix}discountables.type", $types)
                        )
                )
        );
    }

    /**
     * @param  iterable  $exempt  Discount ids that stay usable whatever their
     *                            use count — a cart that already consumed a
     *                            discount must still be able to re-price with it.
     */
    public function scopeUsable(Builder $query, iterable $exempt = []): Builder
    {
        $exempt = collect($exempt)->filter()->values();

        return $query->where(function ($subQuery) use ($exempt) {
            $subQuery->whereRaw('uses < max_uses')
                ->orWhereNull('max_uses')
                ->when(
                    $exempt->isNotEmpty(),
                    fn ($subQuery) => $subQuery->orWhereIn('id', $exempt)
                );
        });
    }
}

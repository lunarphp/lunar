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
class Discount extends BaseModel
{
    use HasChannels,
        HasCustomerGroups,
        HasFactory,
        HasTranslations;

    protected $guarded = [];

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
    protected static function newFactory(): DiscountFactory
    {
        return DiscountFactory::new();
    }

    public function users(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            config('auth.providers.users.model'),
            "{$prefix}discount_user"
        )->withTimestamps();
    }

    /**
     * Return the purchasables relationship.
     */
    public function purchasables(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::class);
    }

    /**
     * Return the purchasable conditions relationship.
     */
    public function purchasableConditions(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::class)->whereType('condition');
    }

    /**
     * Return the purchasable exclusions relationship.
     */
    public function purchasableExclusions(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::class)->whereType('exclusion');
    }

    /**
     * Return the purchasable limitations relationship.
     */
    public function purchasableLimitations(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::class)->whereType('limitation');
    }

    /**
     * Return the purchasable rewards relationship.
     */
    public function purchasableRewards(): HasMany
    {
        return $this->hasMany(DiscountPurchasable::class)->whereType('reward');
    }

    public function getType()
    {
        return app($this->type)->with($this);
    }

    /**
     * Return the collections relationship.
     */
    public function collections(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Collection::class,
            "{$prefix}collection_discount"
        )->withPivot(['type'])->withTimestamps();
    }

    /**
     * Return the customer groups relationship.
     */
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

    /**
     * Return the active scope.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Return the products scope.
     */
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
                        ->wherePurchasableType(Product::class)
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn('type', $types)
                        )
                )
        );
    }

    /**
     * Return the product variants scope.
     */
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
                        ->wherePurchasableType(ProductVariant::class)
                        ->when(
                            $types,
                            fn ($query) => $query->whereIn('type', $types)
                        )
                )
        );
    }

    /**
     * Return when the discount is usable.
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query->where(function ($subQuery) {
            $subQuery->whereRaw('uses < max_uses')
                ->orWhereNull('max_uses');
        });
    }
}

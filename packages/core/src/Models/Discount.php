<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    use HasFactory,
        HasTranslations,
        HasChannels,
        HasCustomerGroups;

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
     *
     * @return DiscountFactory
     */
    protected static function newFactory(): DiscountFactory
    {
        return DiscountFactory::new();
    }

    /**
     * Return the purchasables relationship.
     *
     * @return HasMany
     */
    public function purchasables()
    {
        return $this->hasMany(DiscountPurchasable::class);
    }

    public function purchasableConditions()
    {
        return $this->hasMany(DiscountPurchasable::class)->whereType('condition');
    }

    public function purchasableLimitations()
    {
        return $this->hasMany(DiscountPurchasable::class)->whereType('limitation');
    }

    public function purchasableRewards()
    {
        return $this->hasMany(DiscountPurchasable::class)->whereType('reward');
    }

    public function getType()
    {
        return app($this->type)->with($this);
    }

    /**
     * Return the collections relationship.
     *
     * @return HasMany
     */
    public function collections()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Collection::class,
            "{$prefix}collection_discount"
        )->withTimestamps();
    }

    /**
     * Return the customer groups relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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

    public function brands()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Brand::class,
            "{$prefix}brand_discount"
        )->withTimestamps();
    }

    /**
     * Return the active scope.
     *
     * @param  Builder  $query
     * @return void
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereNotNull('starts_at')
            ->where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }
}

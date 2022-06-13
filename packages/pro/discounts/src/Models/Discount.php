<?php

namespace GetCandy\Discounts\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\AsAttributeData;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Discounts\Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends BaseModel
{
    use HasFactory,
        HasTranslations;

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'attribute_data' => AsAttributeData::class,
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
     * Return the rewards relationship
     *
     * @return HasMany
     */
    public function rewards()
    {
        return $this->hasMany(DiscountReward::class);
    }

    /**
     * Return the conditions relationship
     *
     * @return HasMany
     */
    public function conditions()
    {
        return $this->hasMany(DiscountCondition::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNotNull('starts_at')
            ->whereDate('starts_at', '>=', now())
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>', now());
            });
    }
}

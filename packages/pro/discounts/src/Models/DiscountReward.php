<?php

namespace GetCandy\Discounts\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Discounts\Database\Factories\DiscountRewardFactory;
use GetCandy\Discounts\Facades\DiscountRewards;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountReward extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'data' => 'object',
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return DiscountFactory
     */
    protected static function newFactory(): DiscountRewardFactory
    {
        return DiscountRewardFactory::new();
    }

    /**
     * Return the discount relationship.
     *
     * @return BelongsTo
     */
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function purchasables()
    {
        return $this->morphMany(DiscountPurchasable::class, 'discount');
    }

    public function driver()
    {
        return DiscountRewards::driver($this->driver)->with($this);
    }
}

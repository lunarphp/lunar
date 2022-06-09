<?php

namespace GetCandy\Discounts\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Discounts\Database\Factories\DiscountConditionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GetCandy\Discounts\Facades\DiscountConditions;

class DiscountCondition extends BaseModel
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
    protected static function newFactory(): DiscountConditionFactory
    {
        return DiscountConditionFactory::new();
    }

    /**
     * Return the discount relationship
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
        return DiscountConditions::driver($this->driver);
    }
}

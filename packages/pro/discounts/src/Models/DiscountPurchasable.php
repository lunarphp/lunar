<?php

namespace GetCandy\Discounts\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Discounts\Database\Factories\DiscountRewardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DiscountPurchasable extends BaseModel
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
     * Return the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function purchasable()
    {
        return $this->morphTo();
    }
}

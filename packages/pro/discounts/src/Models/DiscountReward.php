<?php

namespace GetCandy\Discounts;

use GetCandy\Base\BaseModel;

class DiscountReward extends BaseModel
{
    /**
     * Return the discount relationship
     *
     * @return BelongsTo
     */
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}

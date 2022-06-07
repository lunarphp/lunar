<?php

namespace GetCandy\Discounts;

use GetCandy\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCondition extends BaseModel
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

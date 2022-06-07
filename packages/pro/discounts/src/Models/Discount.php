<?php

namespace GetCandy\Discounts;

use GetCandy\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends BaseModel
{
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
}

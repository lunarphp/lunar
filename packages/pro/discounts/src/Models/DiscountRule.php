<?php

namespace GetCandy\Discounts\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Discounts\Database\Factories\DiscountRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GetCandy\Discounts\Facades\DiscountRules;

class DiscountRule extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'data' => 'object',
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return DiscountFactory
     */
    protected static function newFactory(): DiscountRuleFactory
    {
        return DiscountRuleFactory::new();
    }

    /**
     * Return the discount relationship
     *
     * @return BelongsTo
     */
    public function ruleset()
    {
        return $this->belongsTo(Discount::class);
    }

    public function purchasables()
    {
        return $this->morphMany(DiscountPurchasable::class, 'discount');
    }

    public function driver()
    {
        return DiscountRules::driver($this->driver)->with($this);
    }
}

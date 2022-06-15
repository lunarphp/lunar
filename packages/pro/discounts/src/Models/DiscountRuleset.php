<?php

namespace GetCandy\Discounts\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Discounts\Database\Factories\DiscountRulesetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountRuleset extends BaseModel
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    protected $casts = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return DiscountFactory
     */
    protected static function newFactory(): DiscountRulesetFactory
    {
        return DiscountRulesetFactory::new();
    }

    /**
     * Return the rules relationship
     *
     * @return HasMany
     */
    public function rules()
    {
        return $this->hasMany(DiscountRule::class);
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
}

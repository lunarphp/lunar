<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\ProductAssociationFactory;

/**
 * @property int $id
 * @property int $product_parent_id
 * @property int $product_target_id
 * @property string $type
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class ProductAssociation extends BaseModel implements \Lunar\Models\Contracts\ProductAssociation
{
    use HasFactory;
    use HasMacros;

    /**
     * Define the cross-sell type.
     */
    const CROSS_SELL = 'cross-sell';

    /**
     * Define the upsell type.
     */
    const UP_SELL = 'up-sell';

    /**
     * Define the alternate type.
     */
    const ALTERNATE = 'alternate';

    /**
     * Define the fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'product_parent_id',
        'product_target_id',
        'type',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductAssociationFactory::new();
    }

    /**
     * Return the parent relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::modelClass(), 'product_parent_id');
    }

    /**
     * Return the parent relationship.
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Product::modelClass(), 'product_target_id');
    }

    /**
     * Apply the cross-sell scope.
     */
    public function scopeCrossSell(Builder $query): Builder
    {
        return $query->type(self::CROSS_SELL);
    }

    /**
     * Apply the upsell scope.
     */
    public function scopeUpSell(Builder $query): Builder
    {
        return $query->type(self::UP_SELL);
    }

    /**
     * Apply the up alternate scope.
     */
    public function scopeAlternate(Builder $query): Builder
    {
        return $query->type(self::ALTERNATE);
    }

    /**
     * Apply the type scope.
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->whereType($type);
    }
}

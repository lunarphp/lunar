<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Base\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Base\Enums\ProductAssociation as ProductAssociationEnum;
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
class ProductAssociation extends BaseModel implements Contracts\ProductAssociation
{
    use HasFactory;
    use HasMacros;

    /**
     * Define the cross-sell type.
     *
     * @deprecated 1.2.0
     * @see \Lunar\Base\Enums\ProductAssociation
     */
    const CROSS_SELL = 'cross-sell';

    /**
     * Define the upsell type.
     *
     * @deprecated 1.2.0
     * @see \Lunar\Base\Enums\ProductAssociation
     */
    const UP_SELL = 'up-sell';

    /**
     * Define the alternate type.
     *
     * @deprecated 1.2.0
     * @see \Lunar\Base\Enums\ProductAssociation
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
     * Return the target relationship.
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
        return $query->type(ProductAssociationEnum::CROSS_SELL);
    }

    /**
     * Apply the upsell scope.
     */
    public function scopeUpSell(Builder $query): Builder
    {
        return $query->type(ProductAssociationEnum::UP_SELL);
    }

    /**
     * Apply the up alternate scope.
     */
    public function scopeAlternate(Builder $query): Builder
    {
        return $query->type(ProductAssociationEnum::ALTERNATE);
    }

    /**
     * Apply the type scope.
     */
    public function scopeType(Builder $query, ProvidesProductAssociationType|string $type): Builder
    {
        return $query->where(
            'type',
            '=',
            is_string($type) ? $type : $type->value
        );
    }

    public static function getTypes(): array
    {
        $enum = config('lunar.products.association_types_enum', \Lunar\Base\Enums\ProductAssociation::class);

        return collect($enum::cases())->mapWithKeys(function ($item) {
            return [
                $item->value => $item->label(),
            ];
        })->toArray();
    }
}

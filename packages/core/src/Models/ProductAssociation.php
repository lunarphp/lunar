<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\ProductAssociationFactory;
use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Core\Enums\ProductAssociation as ProductAssociationEnum;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * @property int $id
 * @property int $product_parent_id
 * @property int $product_target_id
 * @property string $type
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ProductAssociation extends Base
{
    use HasFactory;
    use HasMacros;

    /**
     * Define the cross-sell type.
     *
     * @deprecated 1.2.0
     * @see ProductAssociationEnum
     */
    const CROSS_SELL = 'cross-sell';

    /**
     * Define the upsell type.
     *
     * @deprecated 1.2.0
     * @see ProductAssociationEnum
     */
    const UP_SELL = 'up-sell';

    /**
     * Define the alternate type.
     *
     * @deprecated 1.2.0
     * @see ProductAssociationEnum
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
        return $this->belongsTo(Product::class, 'product_parent_id');
    }

    /**
     * Return the target relationship.
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_target_id');
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
        $enum = config('lunar.products.association_types_enum', ProductAssociationEnum::class);

        return collect($enum::cases())->mapWithKeys(function ($item) {
            return [
                $item->value => $item->label(),
            ];
        })->toArray();
    }
}

<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\ProductTypeFactory;

/**
 * @property int $id
 * @property string $name
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class ProductType extends BaseModel
{
    use HasAttributes;
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): ProductTypeFactory
    {
        return ProductTypeFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the mapped attributes relation.
     */
    public function mappedAttributes(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Attribute::class,
            'attributable',
            "{$prefix}attributables"
        )->withTimestamps();
    }

    /**
     * Return the product attributes relationship.
     */
    public function productAttributes(): MorphToMany
    {
        return $this->mappedAttributes()->whereAttributeType(Product::class);
    }

    /**
     * Return the variant attributes relationship.
     */
    public function variantAttributes(): MorphToMany
    {
        return $this->mappedAttributes()->whereAttributeType(ProductVariant::class);
    }

    /**
     * Get the products relation.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

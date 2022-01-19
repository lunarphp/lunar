<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasAttributes;
use GetCandy\Database\Factories\ProductTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductType extends BaseModel
{
    use HasAttributes;
    use HasFactory;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\ProductTypeFactory
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function mappedAttributes()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->morphToMany(
            Attribute::class,
            'attributable',
            "{$prefix}attributables"
        )->withTimestamps();
    }

    /**
     * Return the product attributes relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function productAttributes()
    {
        return $this->mappedAttributes()->whereAttributeType(Product::class);
    }

    /**
     * Return the variant attributes relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function variantAttributes()
    {
        return $this->mappedAttributes()->whereAttributeType(ProductVariant::class);
    }

    /**
     * Get the products relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

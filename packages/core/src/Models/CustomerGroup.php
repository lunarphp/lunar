<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Base\Traits\HasDefaultRecord;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\CustomerGroupFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property bool $default
 * @property ?array $attribute_data
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class CustomerGroup extends BaseModel
{
    use HasAttributes;
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'attribute_data' => AsAttributeData::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): CustomerGroupFactory
    {
        return CustomerGroupFactory::new();
    }

    /**
     * Return the customer's relationship.
     */
    public function customers(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Customer::class,
            "{$prefix}customer_customer_group"
        )->withTimestamps();
    }

    /**
     * Return the discounts relationship.
     */
    public function discounts(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Discount::class,
            "{$prefix}customer_group_discount"
        )->withTimestamps();
    }

    /**
     * Return the product relationship.
     */
    public function products(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Product::class,
            "{$prefix}customer_group_product"
        )->withTimestamps();
    }

    /**
     * Return the product relationship.
     */
    public function collections(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Collection::class,
            "{$prefix}collection_customer_group"
        )->withTimestamps();
    }

    /**
     * Get the mapped attributes relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function mappedAttributes()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Attribute::class,
            'attributable',
            "{$prefix}attributables"
        )->withTimestamps();
    }
}

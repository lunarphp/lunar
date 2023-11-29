<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasDefaultRecord;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\CustomerGroupFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property bool $default
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class CustomerGroup extends BaseModel
{
    use HasDefaultRecord;
    use HasFactory;
    use HasMacros;

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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customers()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Customer::class,
            "{$prefix}customer_customer_group"
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
}

<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Database\Factories\CustomerGroupFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerGroup extends BaseModel
{
    use HasFactory;
    use HasDefaultRecord;
    use HasMacros;
    use HasTranslations;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'name' => AsCollection::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CustomerGroupFactory
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
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(
            Customer::class,
            "{$prefix}customer_customer_group"
        )->withTimestamps();
    }
}

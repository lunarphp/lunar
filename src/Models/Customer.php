<?php

namespace GetCandy\Models;

use App\Models\User;
use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasPersonalDetails;
use GetCandy\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends BaseModel
{
    use HasFactory, HasPersonalDetails;

    /**
     * Define the guarded attributes.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CustomerFactory
     */
    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    /**
     * Set the meta value.
     *
     * @param  array|null  $val
     * @return void
     */
    public function setMetaAttribute(array $val = null)
    {
        if ($val) {
            $this->attributes['meta'] = json_encode($val);
        }
    }

    /**
     * Get the meta value.
     *
     * @return array
     */
    public function getMetaAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Return the customer group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customerGroups()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::class,
            "{$prefix}customer_customer_group"
        )->withTimestamps();
    }

    /**
     * Return the customer group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(
            config('auth.providers.users.model'),
            "{$prefix}customer_user"
        )->withTimestamps();
    }
}

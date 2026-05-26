<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Base\BaseModel;
use Lunar\Core\Base\Traits\HasAttributes;
use Lunar\Core\Base\Traits\HasMacros;
use Lunar\Core\Base\Traits\HasPersonalDetails;
use Lunar\Core\Base\Traits\HasTranslations;
use Lunar\Core\Base\Traits\LogsActivity;
use Lunar\Core\Base\Traits\Searchable;
use Lunar\Core\Casts\AsAttributeData;
use Lunar\Core\Database\Factories\CustomerFactory;

/**
 * @property int $id
 * @property ?string $title
 * @property string $first_name
 * @property string $last_name
 * @property ?string $company_name
 * @property ?string $tax_identifier
 * @property ?string $account_ref
 * @property ?array $attribute_data
 * @property ?array $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Customer extends BaseModel implements Contracts\Customer
{
    use HasAttributes;
    use HasFactory;
    use HasMacros;
    use HasPersonalDetails;
    use HasTranslations;
    use LogsActivity;
    use Searchable;

    /**
     * Define the guarded attributes.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'attribute_data' => AsAttributeData::class,
        'meta' => AsArrayObject::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CustomerFactory::new();
    }

    public function customerGroups(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            CustomerGroup::modelClass(),
            "{$prefix}customer_customer_group"
        )->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            config('auth.providers.users.model'),
            "{$prefix}customer_user"
        )->withTimestamps();
    }

    public function discounts(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Discount::modelClass(),
            "{$prefix}customer_discount"
        )->withTimestamps();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::modelClass());
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::modelClass());
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::modelClass());
    }

    public function mappedAttributes(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Attribute::modelClass(),
            'attributable',
            "{$prefix}attributables"
        )->withTimestamps();
    }
}

<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\CustomerFactory;
use Lunar\Core\Models\Concerns\HasAttributeData;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPersonalDetails;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Concerns\Searchable;

/**
 * @property int $id
 * @property string $public_id
 * @property ?string $title
 * @property string $first_name
 * @property string $last_name
 * @property ?string $company_name
 * @property ?string $tax_identifier
 * @property ?string $account_ref
 * @property ?string $admin_notes
 * @property ?array $attribute_data
 * @property ?array $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Customer extends Base
{
    use HasAttributeData;
    use HasFactory;
    use HasMacros;
    use HasPersonalDetails;
    use HasPublicId;
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
            CustomerGroup::class,
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
            Discount::class,
            "{$prefix}customer_discount"
        )->withTimestamps();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function mappedAttributes(): MorphToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->morphToMany(
            Attribute::class,
            'attributable',
            "{$prefix}attributables"
        )->withTimestamps();
    }
}

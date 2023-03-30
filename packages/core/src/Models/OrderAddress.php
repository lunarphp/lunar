<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\Addressable;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasPersonalDetails;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\OrderAddressFactory;

/**
 * @property int $id
 * @property int $order_id
 * @property ?int $country_id
 * @property ?string $title
 * @property ?string $first_name
 * @property ?string $last_name
 * @property ?string $company_name
 * @property ?string $line_one
 * @property ?string $line_two
 * @property ?string $line_three
 * @property ?string $city
 * @property ?string $state
 * @property ?string $postcode
 * @property ?string $delivery_instructions
 * @property ?string $contact_email
 * @property ?string $contact_phone
 * @property string $type
 * @property ?string $shipping_option
 * @property array $meta
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class OrderAddress extends BaseModel implements Addressable
{
    use HasFactory;
    use LogsActivity;
    use HasPersonalDetails;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): OrderAddressFactory
    {
        return OrderAddressFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'country_id',
        'title',
        'first_name',
        'last_name',
        'company_name',
        'line_one',
        'line_two',
        'line_three',
        'city',
        'state',
        'postcode',
        'delivery_instructions',
        'contact_email',
        'contact_phone',
        'meta',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'object',
    ];

    /**
     * Return the order relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Return the country relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}

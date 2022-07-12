<?php

namespace GetCandy\Models;

use GetCandy\Base\Addressable;
use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasPersonalDetails;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Database\Factories\OrderAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderAddress extends BaseModel implements Addressable
{
    use HasFactory;
    use LogsActivity;
    use HasPersonalDetails;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\OrderAddressFactory
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

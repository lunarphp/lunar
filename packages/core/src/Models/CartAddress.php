<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\Addressable;
use Lunar\Base\BaseModel;
use Lunar\Base\ValueObjects\TaxBreakdown;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\CartAddressFactory;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;

class CartAddress extends BaseModel implements Addressable
{
    use HasFactory;
    use LogsActivity;
    use HasMacros;

    /**
     * The applied shipping option.
     *
     * @var ShippingOption|null
     */
    public ?ShippingOption $shippingOption = null;

    /**
     * The shipping sub total.
     *
     * @var \Lunar\DataTypes\Price|null
     */
    public ?Price $shippingSubTotal;

    /**
     * The shipping tax total.
     *
     * @var \Lunar\DataTypes\Price|null
     */
    public ?Price $shippingTaxTotal;

    /**
     * The shipping total.
     *
     * @var \Lunar\DataTypes\Price|null
     */
    public ?Price $shippingTotal;

    /**
     * The tax breakdown.
     *
     * @var \Lunar\Base\ValueObjects\TaxBreakdown
     */
    public TaxBreakdown $taxBreakdown;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\CartAddressFactory
     */
    protected static function newFactory(): CartAddressFactory
    {
        return CartAddressFactory::new();
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
        'shipping_option',
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
     * Return the cart relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
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

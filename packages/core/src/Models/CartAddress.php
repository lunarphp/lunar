<?php

namespace GetCandy\Models;

use GetCandy\Base\Addressable;
use GetCandy\Base\BaseModel;
use GetCandy\Base\DataTransferObjects\TaxBreakdown;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Database\Factories\CartAddressFactory;
use GetCandy\DataTypes\Price;
use GetCandy\DataTypes\ShippingOption;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
     * @var \GetCandy\DataTypes\Price|null
     */
    public ?Price $shippingSubTotal;

    /**
     * The shipping tax total.
     *
     * @var \GetCandy\DataTypes\Price|null
     */
    public ?Price $shippingTaxTotal;

    /**
     * The shipping total.
     *
     * @var \GetCandy\DataTypes\Price|null
     */
    public ?Price $shippingTotal;

    /**
     * The tax breakdown.
     *
     * @var \GetCandy\Base\DataTransferObjects\TaxBreakdown
     */
    public TaxBreakdown $taxBreakdown;

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CartAddressFactory
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

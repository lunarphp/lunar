<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Database\Factories\CartAddressFactory;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Concerns\CachesProperties;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

/**
 * @property int $id
 * @property int $cart_id
 * @property ?int $country_id
 * @property ?string $title
 * @property ?string $first_name
 * @property ?string $last_name
 * @property ?string $company_name
 * @property ?string $tax_identifier
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
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class CartAddress extends Base implements Addressable, Contracts\CartAddress
{
    use CachesProperties;
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    /**
     * Array of cachable class properties.
     *
     * @var array
     */
    public $cachableProperties = [
        'shippingOption',
        'shippingSubTotal',
        'shippingTaxTotal',
        'shippingTotal',
        'taxBreakdown',
    ];

    /**
     * The applied shipping option.
     */
    public ?ShippingOption $shippingOption = null;

    /**
     * The shipping sub total.
     */
    public ?PriceValue $shippingSubTotal = null;

    /**
     * The shipping tax total.
     */
    public ?PriceValue $shippingTaxTotal = null;

    /**
     * The shipping total.
     */
    public ?PriceValue $shippingTotal = null;

    /**
     * The tax breakdown.
     */
    public ?TaxBreakdown $taxBreakdown = null;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
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
        'tax_identifier',
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
        'meta' => AsArrayObject::class,
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}

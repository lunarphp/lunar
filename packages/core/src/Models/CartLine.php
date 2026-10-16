<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Casts\TaxBreakdown as TaxBreakdownCast;
use Lunar\Core\Database\Factories\CartLineFactory;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Concerns\CachesProperties;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

/**
 * @property int $id
 * @property string $public_id
 * @property int $cart_id
 * @property string $purchasable_type
 * @property int $purchasable_id
 * @property int $quantity
 * @property ?array $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class CartLine extends Base
{
    use CachesProperties;
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use LogsActivity;

    /**
     * Array of cachable class properties.
     *
     * @var array
     */
    public $cachableProperties = [
        'unitPrice',
        'unitPriceInclTax',
        'subTotal',
        'subTotalDiscounted',
        'discountTotal',
        'taxAmount',
        'total',
        'promotionDescription',
        'taxBreakdown',
    ];

    /**
     * The cart line unit price.
     */
    public ?PriceValue $unitPrice = null;

    /**
     * The cart line unit price.
     */
    public ?PriceValue $unitPriceInclTax = null;

    /**
     * The cart line sub total.
     */
    public ?PriceValue $subTotal = null;

    /**
     * The discounted sub total
     */
    public ?PriceValue $subTotalDiscounted = null;

    /**
     * The discount total.
     */
    public ?PriceValue $discountTotal = null;

    /**
     * The cart line tax amount.
     */
    public ?PriceValue $taxAmount = null;

    /**
     * The cart line total.
     */
    public ?PriceValue $total = null;

    /**
     * The promotion description.
     */
    public string $promotionDescription = '';

    /**
     * All the tax breakdowns for the cart line.
     */
    public TaxBreakdown $taxBreakdown;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CartLineFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'meta' => AsArrayObject::class,
        'tax_breakdown' => TaxBreakdownCast::class,
    ];

    /**
     * Columns written by the persisted totals snapshot (spec 0076); kept out
     * of the activity log.
     *
     * @return array<int, string>
     */
    public static function totalsColumns(): array
    {
        return [
            'unit_price',
            'unit_price_incl_tax',
            'sub_total',
            'sub_total_discounted',
            'discount_total',
            'tax_total',
            'total',
            'tax_breakdown',
            'promotion_description',
        ];
    }

    public static function getDefaultLogExcept(): array
    {
        return static::totalsColumns();
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function taxClass(): HasOneThrough
    {
        return $this->hasOneThrough(
            TaxClass::class,
            $this->purchasable_type,
            'tax_class_id',
            'id'
        );
    }

    public function discounts(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Discount::class,
            "{$prefix}cart_line_discount"
        );
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
}

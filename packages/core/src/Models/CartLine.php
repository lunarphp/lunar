<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\CachesProperties;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Database\Factories\CartLineFactory;
use Lunar\DataTypes\Price;

/**
 * @property int $id
 * @property int $cart_id
 * @property string $purchasable_type
 * @property int $purchasable_id
 * @property int $quantity
 * @property ?array $meta
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class CartLine extends BaseModel
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
        'unitPrice',
        'subTotal',
        'discountTotal',
        'taxAmount',
        'total',
        'promotionDescription',
        'taxBreakdown',
    ];

    /**
     * The cart line unit price.
     */
    public ?Price $unitPrice = null;

    /**
     * The cart line sub total.
     */
    public ?Price $subTotal = null;

    /**
     * The discounted sub total
     */
    public ?Price $subTotalDiscounted = null;

    /**
     * The discount total.
     */
    public ?Price $discountTotal = null;

    /**
     * The cart line tax amount.
     */
    public ?Price $taxAmount = null;

    /**
     * The cart line total.
     */
    public ?Price $total = null;

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
    protected static function newFactory(): CartLineFactory
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
    ];

    /**
     * Return the cart relationship.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Return the tax class relationship.
     */
    public function taxClass(): HasOneThrough
    {
        return $this->hasOneThrough(
            TaxClass::class,
            $this->purchasable_type,
            'tax_class_id',
            'id'
        );
    }

    /**
     * Return the cart line discount.
     */
    public function discounts(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Discount::class,
            "{$prefix}cart_line_discount"
        );
    }

    /**
     * Return the polymorphic relation.
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
}

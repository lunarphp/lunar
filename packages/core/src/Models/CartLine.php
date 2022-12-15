<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\CachesProperties;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Database\Factories\CartLineFactory;
use Lunar\DataTypes\Price;

class CartLine extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use HasMacros;
    use CachesProperties;

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
     *
     * @var null|Price
     */
    public ?Price $unitPrice = null;

    /**
     * The cart line sub total.
     *
     * @var null|Price
     */
    public ?Price $subTotal = null;

    /**
     * The discount total.
     *
     * @var null|Price
     */
    public ?Price $discountTotal = null;

    /**
     * The cart line tax amount.
     *
     * @var null|Price
     */
    public ?Price $taxAmount = null;

    /**
     * The cart line total.
     *
     * @var null|Price
     */
    public ?Price $total = null;

    /**
     * The promotion description.
     *
     * @var string
     */
    public string $promotionDescription = '';

    /**
     * All the tax breakdowns for the cart line.
     *
     * @var \Lunar\Base\ValueObjects\Cart\TaxBreakdown
     */
    public TaxBreakdown $taxBreakdown;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\CartLineFactory
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
     * Return the tax class relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function taxClass()
    {
        return $this->hasOneThrough(
            TaxClass::class,
            $this->purchasable_type,
            'tax_class_id',
            'id'
        );
    }

    public function discounts()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            Discount::class,
            "{$prefix}cart_line_discount"
        );
    }

    /**
     * Return the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function purchasable()
    {
        return $this->morphTo();
    }
}

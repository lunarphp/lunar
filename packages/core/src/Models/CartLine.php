<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\ValueObjects\TaxBreakdown;
use Lunar\Database\Factories\CartLineFactory;
use Lunar\DataTypes\Price;

class CartLine extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use HasMacros;

    /**
     * The cart line total.
     *
     * @var Price|null
     */
    public ?Price $total = null;

    /**
     * The cart line sub total.
     *
     * @var Price|null
     */
    public ?Price $subTotal = null;

    /**
     * The cart line tax amount.
     *
     * @var Price|null
     */
    public ?Price $taxAmount = null;

    /**
     * The cart line unit price.
     *
     * @var Price|null
     */
    public ?Price $unitPrice = null;

    /**
     * The discount total.
     *
     * @var Price|null
     */
    public ?Price $discountTotal = null;

    /**
     * All the tax breakdowns for the cart line.
     *
     * @var \Lunar\Base\ValueObjects\TaxBreakdown
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

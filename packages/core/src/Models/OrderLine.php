<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\Price;
use Lunar\Base\Casts\TaxBreakdown;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\OrderLineFactory;

class OrderLine extends BaseModel
{
    use LogsActivity;
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\OrderLineFactory
     */
    protected static function newFactory(): OrderLineFactory
    {
        return OrderLineFactory::new();
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
        'unit_quantity'  => 'integer',
        'quantity'       => 'integer',
        'meta'           => 'object',
        'tax_breakdown'  => TaxBreakdown::class,
        'unit_price'     => Price::class,
        'sub_total'      => Price::class,
        'tax_total'      => Price::class,
        'discount_total' => Price::class,
        'total'          => Price::class,
    ];

    /**
     * Return the URL of the image thumbnail.
     *
     * @param  string  $size
     * @return string
     */
    public function getThumbnail(string $size = 'small'): string|null
    {
        return $this->purchasable->images->first()?->getUrl($size)
            ?: $this->purchasable->product->media->first()?->getUrl($size);
    }

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
     * Return the polymorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function purchasable()
    {
        return $this->morphTo();
    }

    /**
     * Return the currency relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function currency()
    {
        return $this->hasOneThrough(
            Currency::class,
            Order::class,
            'id',
            'code',
            'order_id',
            'currency_code'
        );
    }
}

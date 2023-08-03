<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\Price as CastsPrice;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\PriceFactory;
use Spatie\LaravelBlink\BlinkFacade as Blink;

/**
 * @property int $id
 * @property ?int $customer_group_id
 * @property ?int $currency_id
 * @property string $priceable_type
 * @property int $priceable_id
 * @property int $price
 * @property ?int $compare_price
 * @property int $tier
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Price extends BaseModel
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): PriceFactory
    {
        return PriceFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'price' => CastsPrice::class,
        'compare_price' => CastsPrice::class,
    ];

    /**
     * Return the priceable relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function priceable()
    {
        return $this->morphTo();
    }

    /**
     * Return the currency relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Return the customer group relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Return the price exclusive of tax.
     *
     * @return \Lunar\DataTypes\Price
     */
    public function priceExTax()
    {
        if (! prices_inc_tax()) {
            return $this->price;
        }

        $priceExTax = clone $this->price;

        $priceExTax->value = (int) round($priceExTax->value / (1 + $this->getDefaultTaxRate()));

        return $priceExTax;
    }

    /**
     * Return the price inclusive of tax.
     *
     * @return \Lunar\DataTypes\Price
     */
    public function priceIncTax()
    {
        if (prices_inc_tax()) {
            return $this->price;
        }

        $priceIncTax = clone $this->price;
        $priceIncTax->value = (int) round($priceIncTax->value * (1 + $this->getDefaultTaxRate()));

        return $priceIncTax;
    }

    protected function getDefaultTaxRate()
    {
        return Blink::once('price_default_tax_rate', function () {
            $taxZone = TaxZone::where('default', '=', 1)->first();

            if ($taxZone) {
                if (! is_null($taxClass = $this->priceable?->getTaxClass())) {
                    return $taxZone->taxAmounts->where('tax_class_id', $taxClass->id)->sum('percentage') / 100;
                } else {
                    return $taxZone->taxAmounts->sum('percentage') / 100;
                }
            }

            return 0;
        });
    }
}

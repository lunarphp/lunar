<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
 * @property \Lunar\DataTypes\Price $price
 * @property ?int $compare_price
 * @property int $min_quantity
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Price extends BaseModel implements Contracts\Price
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
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
     */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Return the currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Return the customer group relationship.
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Return the price exclusive of tax.
     */
    public function priceExTax(): \Lunar\DataTypes\Price
    {
        if (! prices_inc_tax()) {
            return $this->price;
        }

        $priceExTax = clone $this->price;

        $priceExTax->value = (int) round($priceExTax->value / (1 + $this->getPriceableTaxRate()));

        return $priceExTax;
    }

    /**
     * Return the price inclusive of tax.
     */
    public function priceIncTax(): int|\Lunar\DataTypes\Price
    {
        if (prices_inc_tax()) {
            return $this->price;
        }

        $priceIncTax = clone $this->price;
        $priceIncTax->value = (int) round($priceIncTax->value * (1 + $this->getPriceableTaxRate()));

        return $priceIncTax;
    }

    /**
     * Return the total tax rate amount within the predefined tax zone for the related priceable
     */
    protected function getPriceableTaxRate(): int|float
    {
        return Blink::once('price_tax_rate_'.$this->priceable->getTaxClass()->id, function () {
            $taxZone = TaxZone::where('default', '=', 1)->first();

            if ($taxZone && ! is_null($taxClass = $this->priceable->getTaxClass())) {
                return $taxClass->taxRateAmounts
                    ->whereIn('tax_rate_id', $taxZone->taxRates->pluck('id'))
                    ->sum('percentage') / 100;
            }

            return 0;
        });
    }
}

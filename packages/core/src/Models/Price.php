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
     *
     * @param  TaxZone|null  $taxZone  Optional override for the tax zone. Falls back to the
     *                                 Blink cart override, then the store's default zone.
     */
    public function priceExTax(?TaxZone $taxZone = null): \Lunar\DataTypes\Price
    {
        if (! prices_inc_tax()) {
            return $this->price;
        }

        $priceExTax = clone $this->price;

        $priceExTax->value = (int) round($priceExTax->value / (1 + $this->getPriceableTaxRate($taxZone)));

        return $priceExTax;
    }

    /**
     * Return the price inclusive of tax.
     *
     * @param  TaxZone|null  $taxZone  Optional override for the tax zone.
     */
    public function priceIncTax(?TaxZone $taxZone = null): int|\Lunar\DataTypes\Price
    {
        if (prices_inc_tax()) {
            return $this->price;
        }

        $priceIncTax = clone $this->price;
        $priceIncTax->value = (int) round($priceIncTax->value * (1 + $this->getPriceableTaxRate($taxZone)));

        return $priceIncTax;
    }

    /**
     * Return the compare price inclusive of tax.
     *
     * @param  TaxZone|null  $taxZone  Optional override for the tax zone.
     */
    public function comparePriceIncTax(?TaxZone $taxZone = null): int|\Lunar\DataTypes\Price
    {
        if (prices_inc_tax()) {
            return $this->compare_price;
        }

        $comparePriceIncTax = clone $this->compare_price;
        $comparePriceIncTax->value = (int) round($comparePriceIncTax->value * (1 + $this->getPriceableTaxRate($taxZone)));

        return $comparePriceIncTax;
    }

    /**
     * Return the total tax rate percentage (as a decimal, e.g. 0.20 for 20 %) for the given
     * tax zone + priceable's own tax class combination.
     *
     * Resolution order
     * ─ Tax class : always from the priceable (product-level classification, never overridden here)
     * ─ Tax zone  : explicit param → Blink cart override (lunar_cart_tax_zone) → store default zone
     *
     * Results are cached in Blink keyed by "{classId}_{zoneId}" so different combinations
     * never collide within the same request.
     */
    protected function getPriceableTaxRate(?TaxZone $taxZone = null): int|float
    {
        // Tax class always comes from the priceable (item classification stays on the product)
        $taxClass = $this->priceable->getTaxClass();

        // Resolve tax zone: explicit param → Blink cart override → cached store default zone
        $taxZone ??= Blink::get('lunar_cart_tax_zone')
            ?? Blink::once('lunar_default_tax_zone', fn () => TaxZone::where('default', '=', 1)->first());

        $cacheKey = 'price_tax_rate_'.$taxClass->id.'_'.($taxZone?->id ?? 'none');

        return Blink::once($cacheKey, function () use ($taxClass, $taxZone) {
            if ($taxZone && $taxClass) {
                return $taxClass->taxRateAmounts
                    ->whereIn('tax_rate_id', $taxZone->taxRates->pluck('id'))
                    ->sum('percentage') / 100;
            }

            return 0;
        });
    }
}

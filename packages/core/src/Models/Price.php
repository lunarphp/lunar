<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Base\BaseModel;
use Lunar\Core\Base\Casts\Price as CastsPrice;
use Lunar\Core\Base\Traits\HasMacros;
use Lunar\Core\Database\Factories\PriceFactory;
use Lunar\Core\Models\Contracts\TaxZone as TaxZoneContract;
use Spatie\LaravelBlink\BlinkFacade as Blink;

/**
 * @property int $id
 * @property ?int $customer_group_id
 * @property ?int $currency_id
 * @property string $priceable_type
 * @property int $priceable_id
 * @property \Lunar\Core\DataTypes\Price $price
 * @property ?int $compare_price
 * @property int $min_quantity
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
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
     * @param  TaxZone|null  $taxZone  Optional override for the tax zone. Falls back to the store's default zone.
     */
    public function priceExTax(?TaxZoneContract $taxZone = null): \Lunar\Core\DataTypes\Price
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
    public function priceIncTax(?TaxZoneContract $taxZone = null): int|\Lunar\Core\DataTypes\Price
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
    public function comparePriceIncTax(?TaxZoneContract $taxZone = null): int|\Lunar\Core\DataTypes\Price
    {
        if (prices_inc_tax()) {
            return $this->compare_price;
        }

        $comparePriceIncTax = clone $this->compare_price;
        $comparePriceIncTax->value = (int) round($comparePriceIncTax->value * (1 + $this->getPriceableTaxRate($taxZone)));

        return $comparePriceIncTax;
    }

    /**
     * Return the total tax rate (as a decimal, e.g. 0.20 = 20%) for the given tax zone
     * combined with the priceable's own tax class.
     *
     * Tax zone resolution: explicit param → store default zone.
     * Results are memoised per "{classId}_{zoneId}" so unrelated combinations never collide.
     */
    protected function getPriceableTaxRate(?TaxZoneContract $taxZone = null): int|float
    {
        $taxClass = $this->priceable->getTaxClass();
        $taxZone ??= Blink::once('lunar_default_tax_zone', fn () => TaxZone::where('default', '=', 1)->first());
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

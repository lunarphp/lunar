<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Database\Factories\PriceFactory;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Concerns\FormatsPrices;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Pricing\PriceFormatterInterface;
use Spatie\LaravelBlink\BlinkFacade as Blink;

/**
 * @property int $id
 * @property ?int $customer_group_id
 * @property ?int $currency_id
 * @property string $priceable_type
 * @property int $priceable_id
 * @property int $price
 * @property ?int $list_price
 * @property int $min_quantity
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Price extends Base implements HasCurrency
{
    use FormatsPrices;
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
        'price' => 'integer',
        'list_price' => 'integer',
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

    public function resolveCurrency(): Currency
    {
        $this->loadMissing('currency');

        return $this->currency ?? Currency::getDefault();
    }

    /**
     * Format the given money column divided by the priceable's
     * `unit_quantity` — e.g. display the per-single-unit figure for a
     * pack price stored against a variant with `unit_quantity > 1`.
     */
    public function unitFormat(string $field, ?string $locale = null, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): ?string
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return null;
        }

        return app(PriceFormatterInterface::class, [
            'value' => (int) $value,
            'currency' => $this->resolveCurrency(),
            'unitQty' => $this->resolvePriceableUnitQuantity(),
        ])->unitFormatted($locale, decimalPlaces: $decimalPlaces, trimTrailingZeros: $trimTrailingZeros);
    }

    /**
     * Decimal form of {@see unitFormat()} — the per-single-unit value of
     * the given money column in the priceable's currency, as a float.
     */
    public function unitDecimal(string $field, bool $rounding = true): ?float
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return null;
        }

        return app(PriceFormatterInterface::class, [
            'value' => (int) $value,
            'currency' => $this->resolveCurrency(),
            'unitQty' => $this->resolvePriceableUnitQuantity(),
        ])->unitDecimal($rounding);
    }

    private function resolvePriceableUnitQuantity(): int
    {
        $this->loadMissing('priceable');

        return (int) ($this->priceable->unit_quantity ?? 1);
    }

    /**
     * Return the price exclusive of tax.
     *
     * @param  TaxZone|null  $taxZone  Optional override for the tax zone. Falls back to the store's default zone.
     */
    public function priceExTax(?TaxZone $taxZone = null): PriceValue
    {
        $value = (int) $this->price;
        $currency = $this->resolveCurrency();

        if (prices_inc_tax()) {
            $value = PriceCalculator::withoutTax(
                $value,
                (float) $this->getPriceableTaxRate($taxZone),
                $currency,
            );
        }

        return new PriceValue($value, $currency);
    }

    /**
     * Return the price inclusive of tax.
     *
     * @param  TaxZone|null  $taxZone  Optional override for the tax zone.
     */
    public function priceIncTax(?TaxZone $taxZone = null): PriceValue
    {
        $value = (int) $this->price;
        $currency = $this->resolveCurrency();

        if (! prices_inc_tax()) {
            $value = PriceCalculator::withTax(
                $value,
                (float) $this->getPriceableTaxRate($taxZone),
                $currency,
            );
        }

        return new PriceValue($value, $currency);
    }

    /**
     * Return the list price inclusive of tax.
     *
     * @param  TaxZone|null  $taxZone  Optional override for the tax zone.
     */
    public function listPriceIncTax(?TaxZone $taxZone = null): PriceValue
    {
        $value = (int) $this->list_price;
        $currency = $this->resolveCurrency();

        if (! prices_inc_tax()) {
            $value = PriceCalculator::withTax(
                $value,
                (float) $this->getPriceableTaxRate($taxZone),
                $currency,
            );
        }

        return new PriceValue($value, $currency);
    }

    /**
     * Return the total tax rate (as a decimal, e.g. 0.20 = 20%) for the given tax zone
     * combined with the priceable's own tax class.
     *
     * Tax zone resolution: explicit param → store default zone.
     * Results are memoised per "{classId}_{zoneId}" so unrelated combinations never collide.
     */
    protected function getPriceableTaxRate(?TaxZone $taxZone = null): int|float
    {
        $taxClass = $this->priceable->getTaxClass();
        $taxZone ??= Blink::once('lunar_default_tax_zone', fn () => TaxZone::where('default', '=', 1)->first());
        $cacheKey = 'price_tax_rate_'.$taxClass->id.'_'.($taxZone?->id ?? 'none');

        return Blink::once($cacheKey, function () use ($taxClass, $taxZone) {
            if ($taxZone && $taxClass) {
                $taxClass->loadMissing('taxRateAmounts');
                $taxZone->loadMissing('taxRates');

                return $taxClass->taxRateAmounts
                    ->whereIn('tax_rate_id', $taxZone->taxRates->pluck('id'))
                    ->sum('percentage') / 100;
            }

            return 0;
        });
    }
}

<?php

namespace Lunar\Panel\Support;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\PriceCalculatorInterface;

/**
 * The part of a discount's `data` column that core owns rather than the type.
 *
 * `min_prices` is read by AbstractDiscountType::checkDiscountConditions() for
 * every discount type, so it cannot belong to any one type's form — a form
 * returning only its own keys from toStorage() would drop it. Keeping it here
 * means the minimum-spend condition survives whatever a third-party type form
 * does with the rest of the column.
 */
class DiscountConditionsSchema
{
    public function __construct(protected PriceCalculatorInterface $priceCalculator) {}

    /**
     * Stored minor units to the decimal amounts the form edits, one entry per
     * enabled currency so the inputs always render.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, ?float>
     */
    public function toForm(array $data): array
    {
        $stored = (array) ($data['min_prices'] ?? []);

        return $this->currencies()
            ->mapWithKeys(fn (Currency $currency) => [
                $currency->code => isset($stored[$currency->code])
                    ? $this->priceCalculator->toMajor((int) $stored[$currency->code], $currency)
                    : null,
            ])
            ->all();
    }

    /**
     * The `min_prices` map to store, scaled back to minor units through the
     * currency's own decimal places. A blank amount drops the entry rather than
     * storing a zero, which reads as "no minimum" to the condition check.
     *
     * @param  array<string, mixed>  $form
     * @return array<string, int>
     */
    public function toStorage(array $form): array
    {
        $submitted = (array) ($form['min_prices'] ?? []);

        return $this->currencies()
            ->filter(fn (Currency $currency) => filled($submitted[$currency->code] ?? null))
            ->mapWithKeys(fn (Currency $currency) => [
                $currency->code => $this->priceCalculator->toMinor($submitted[$currency->code], $currency),
            ])
            ->all();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $rules = ['min_prices' => ['nullable', 'array']];

        foreach ($this->currencies() as $currency) {
            $rules["min_prices.{$currency->code}"] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    /** @return Collection<int, Currency> */
    protected function currencies(): Collection
    {
        return Currency::query()->whereEnabled(true)->orderByDesc('default')->orderBy('code')->get();
    }
}

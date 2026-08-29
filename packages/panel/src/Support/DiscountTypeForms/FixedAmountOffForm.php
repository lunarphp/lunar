<?php

namespace Lunar\Panel\Support\DiscountTypeForms;

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\PriceCalculatorInterface;
use Lunar\Panel\Contracts\DiscountTypeForm;

/**
 * FixedAmountOff stores `data.amounts` as minor units keyed by currency code,
 * so every value crosses the form boundary through the currency's own decimal
 * places — a zero-decimal currency must not pick up a hardcoded factor of 100.
 */
class FixedAmountOffForm implements DiscountTypeForm
{
    public function __construct(protected PriceCalculatorInterface $priceCalculator) {}

    public function component(): string
    {
        return 'FixedAmountOffForm';
    }

    public function targetBuckets(): array
    {
        return ['limitation', 'exclusion'];
    }

    public function toForm(array $data): array
    {
        $stored = (array) ($data['amounts'] ?? []);

        return [
            'amounts' => $this->currencies()
                ->mapWithKeys(fn (Currency $currency) => [
                    $currency->code => isset($stored[$currency->code])
                        ? $this->priceCalculator->toMajor((int) $stored[$currency->code], $currency)
                        : null,
                ])
                ->all(),
        ];
    }

    public function toStorage(array $data): array
    {
        $submitted = (array) ($data['amounts'] ?? []);

        return [
            'amounts' => $this->currencies()
                ->filter(fn (Currency $currency) => filled($submitted[$currency->code] ?? null))
                ->mapWithKeys(fn (Currency $currency) => [
                    $currency->code => $this->priceCalculator->toMinor($submitted[$currency->code], $currency),
                ])
                ->all(),
        ];
    }

    public function rules(): array
    {
        $rules = ['amounts' => ['required', 'array']];

        foreach ($this->currencies() as $currency) {
            $rules["amounts.{$currency->code}"] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function summary(array $data, ?Currency $currency): ?string
    {
        $amount = $currency ? ($data['amounts'][$currency->code] ?? null) : null;

        if (! $currency || $amount === null) {
            return null;
        }

        return __('panel::discounts.summary_fixed_amount_off', [
            'amount' => (new PriceValue((int) $amount, $currency))->format(),
        ]);
    }

    /** @return Collection<int, Currency> */
    protected function currencies(): Collection
    {
        return Currency::query()->whereEnabled(true)->orderByDesc('default')->orderBy('code')->get();
    }
}

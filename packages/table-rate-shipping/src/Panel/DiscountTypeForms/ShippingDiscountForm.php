<?php

namespace Lunar\Shipping\Panel\DiscountTypeForms;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\PriceCalculatorInterface;
use Lunar\Panel\Contracts\DiscountTypeForm;
use Lunar\Shipping\Models\ShippingMethod;

/**
 * The panel form for ShippingDiscount. `data.methods` is a list of rules, each
 * naming a shipping method (or none for a catch-all), a type, and either a
 * percentage off or the resulting shipping price per currency in minor units.
 */
class ShippingDiscountForm implements DiscountTypeForm
{
    public function __construct(protected PriceCalculatorInterface $priceCalculator) {}

    public function component(): string
    {
        return 'shipping::ShippingDiscountForm';
    }

    /** The type rewrites the shipping breakdown; it never targets lines. */
    public function targetBuckets(): array
    {
        return [];
    }

    public function toForm(array $data): array
    {
        $currencies = $this->currencies();

        return [
            'methods' => collect($data['methods'] ?? [])->values()->map(function (array $rule) use ($currencies): array {
                $prices = (array) ($rule['prices'] ?? []);

                return [
                    'shipping_method_id' => isset($rule['shipping_method_id']) ? (int) $rule['shipping_method_id'] : null,
                    'type' => $rule['type'] ?? 'fixed',
                    'percentage' => $rule['percentage'] ?? null,
                    'prices' => $currencies->mapWithKeys(fn (Currency $currency) => [
                        $currency->code => isset($prices[$currency->code])
                            ? $this->priceCalculator->toMajor((int) $prices[$currency->code], $currency)
                            : null,
                    ])->all(),
                ];
            })->all(),
        ];
    }

    public function toStorage(array $data): array
    {
        $currencies = $this->currencies();

        return [
            'methods' => collect($data['methods'] ?? [])->values()->map(function (array $rule) use ($currencies): array {
                $type = $rule['type'] ?? 'fixed';
                $submitted = (array) ($rule['prices'] ?? []);

                $stored = [
                    'shipping_method_id' => filled($rule['shipping_method_id'] ?? null) ? (int) $rule['shipping_method_id'] : null,
                    'type' => $type,
                ];

                if ($type === 'percentage') {
                    $stored['percentage'] = (float) ($rule['percentage'] ?? 0);
                } else {
                    $stored['prices'] = $currencies
                        ->filter(fn (Currency $currency) => filled($submitted[$currency->code] ?? null))
                        ->mapWithKeys(fn (Currency $currency) => [
                            $currency->code => $this->priceCalculator->toMinor($submitted[$currency->code], $currency),
                        ])
                        ->all();
                }

                return $stored;
            })->all(),
        ];
    }

    public function rules(): array
    {
        $rules = [
            'methods' => ['present', 'array'],
            'methods.*.shipping_method_id' => ['nullable', Rule::exists(ShippingMethod::class, 'id')],
            'methods.*.type' => ['required', Rule::in(['fixed', 'percentage'])],
            'methods.*.percentage' => ['nullable', 'numeric', 'between:0,100', 'required_if:methods.*.type,percentage'],
            'methods.*.prices' => ['nullable', 'array'],
        ];

        foreach ($this->currencies() as $currency) {
            $rules["methods.*.prices.{$currency->code}"] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function summary(array $data, ?Currency $currency): ?string
    {
        $rules = collect($data['methods'] ?? []);

        if ($rules->isEmpty()) {
            return null;
        }

        $types = $rules->map(fn (array $rule) => $rule['type'] ?? 'fixed')->unique();

        if ($types->count() > 1) {
            return null;
        }

        if ($types->first() === 'percentage') {
            $percentages = $rules->map(fn (array $rule) => (float) ($rule['percentage'] ?? 0))->unique();

            return $percentages->count() === 1
                ? __('shipping::discounts.shipping_discount.summary_percentage', ['percentage' => rtrim(rtrim(number_format($percentages->first(), 2, '.', ''), '0'), '.')])
                : null;
        }

        if (! $currency) {
            return null;
        }

        $amounts = $rules
            ->map(fn (array $rule) => $rule['prices'][$currency->code] ?? null)
            ->filter(fn ($amount) => $amount !== null)
            ->map(fn ($amount) => (int) $amount);

        if ($amounts->isEmpty()) {
            return null;
        }

        if ($amounts->max() === 0) {
            return __('shipping::discounts.shipping_discount.summary_free');
        }

        return __('shipping::discounts.shipping_discount.summary_from', [
            'amount' => (new PriceValue($amounts->min(), $currency))->format(),
        ]);
    }

    /** @return Collection<int, Currency> */
    protected function currencies(): Collection
    {
        return Currency::query()->whereEnabled(true)->orderByDesc('default')->orderBy('code')->get();
    }
}

<?php

namespace Lunar\Shipping\Panel\Support;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\PriceCalculatorInterface;

/**
 * Crosses a shipping method's driver `data` between storage and the edit
 * form. The only money inside it is the free-shipping driver's per-currency
 * minimum spend, stored in minor units and edited in major units; everything
 * else passes through untouched.
 */
class MethodData
{
    public const SCHEDULE_DAYS = [1, 2, 3, 4, 5, 6, 7];

    public function __construct(protected PriceCalculatorInterface $priceCalculator) {}

    /**
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    public function toForm(array $stored): array
    {
        $currencies = $this->currencies();
        $minimum = $stored['minimum_spend'] ?? null;

        // A pre-panel install may hold a scalar minimum; treat it as the
        // default currency's value so it stays visible and editable.
        if (is_scalar($minimum)) {
            $default = $currencies->firstWhere('default', true);
            $minimum = $default ? [$default->code => $minimum] : [];
        }

        return [
            'charge_by' => $stored['charge_by'] ?? 'cart_total',
            'use_discount_amount' => (bool) ($stored['use_discount_amount'] ?? false),
            'minimum_spend' => $currencies->mapWithKeys(fn (Currency $currency) => [
                $currency->code => isset($minimum[$currency->code])
                    ? $this->priceCalculator->toMajor((int) $minimum[$currency->code], $currency)
                    : null,
            ])->all(),
            'schedule' => $this->scheduleToForm($stored['schedule'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    public function toStorage(array $form): array
    {
        $currencies = $this->currencies();
        $data = [];

        if (array_key_exists('charge_by', $form)) {
            $data['charge_by'] = $form['charge_by'] ?: null;
        }

        if (array_key_exists('use_discount_amount', $form)) {
            $data['use_discount_amount'] = (bool) $form['use_discount_amount'];
        }

        if (array_key_exists('minimum_spend', $form)) {
            $submitted = (array) ($form['minimum_spend'] ?? []);

            $minor = $currencies
                ->filter(fn (Currency $currency) => filled($submitted[$currency->code] ?? null))
                ->mapWithKeys(fn (Currency $currency) => [
                    $currency->code => $this->priceCalculator->toMinor($submitted[$currency->code], $currency),
                ])
                ->all();

            $data['minimum_spend'] = $minor ?: null;
        }

        if (array_key_exists('schedule', $form)) {
            $data['schedule'] = $this->scheduleToStorage($form['schedule']);
        }

        return $data;
    }

    /**
     * Null when the method has no schedule (always available); otherwise a
     * row per ISO weekday in the shape ShippingMethod::isAvailable() reads.
     *
     * @return array<string, array{enabled: bool, from: ?string, to: ?string}>|null
     */
    protected function scheduleToForm(mixed $stored): ?array
    {
        if (! is_array($stored) || $stored === []) {
            return null;
        }

        $rows = [];

        foreach (self::SCHEDULE_DAYS as $day) {
            $row = (array) ($stored[(string) $day] ?? $stored[$day] ?? []);

            $rows[(string) $day] = [
                'enabled' => (bool) ($row['enabled'] ?? false),
                'from' => $row['from'] ?? null,
                'to' => $row['to'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, array{enabled: bool, from: ?string, to: ?string}>|null
     */
    protected function scheduleToStorage(mixed $form): ?array
    {
        if (! is_array($form)) {
            return null;
        }

        $rows = [];

        foreach (self::SCHEDULE_DAYS as $day) {
            $row = (array) ($form[(string) $day] ?? $form[$day] ?? []);
            $enabled = (bool) ($row['enabled'] ?? false);

            $rows[(string) $day] = [
                'enabled' => $enabled,
                'from' => $enabled ? ($row['from'] ?: null) : null,
                'to' => $enabled ? ($row['to'] ?: null) : null,
            ];
        }

        return $rows;
    }

    /** @return Collection<int, Currency> */
    protected function currencies(): Collection
    {
        return Currency::query()->whereEnabled(true)->orderByDesc('default')->orderBy('code')->get();
    }
}

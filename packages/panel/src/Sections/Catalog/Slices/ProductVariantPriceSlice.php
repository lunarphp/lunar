<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Panel\Forms\FormSlice;

/**
 * A variant's prices, one field per enabled currency code (`price:USD`),
 * each a list of `{customer_group_id, min_quantity, price, list_price}` rows
 * in minor units. The base row has no customer group and a minimum quantity
 * of 1. Commit makes the variant's rows in each drafted currency exactly the
 * listed ones; currencies without a field (disabled ones) keep their rows.
 */
class ProductVariantPriceSlice extends FormSlice
{
    public const KEY = 'price';

    /** @var array<string, int>|null enabled currency code to id, read once per request */
    protected ?array $currencies = null;

    public function model(): string
    {
        return ProductVariant::class;
    }

    public function key(): string
    {
        return self::KEY;
    }

    public function fields(Model $record): array
    {
        return array_keys($this->currencies());
    }

    public function currentValues(Model $record): array
    {
        /** @var ProductVariant $record */
        $rows = $record->exists ? $record->prices()->get()->groupBy('currency_id') : collect();

        $values = [];

        foreach ($this->currencies() as $code => $currencyId) {
            $values[$code] = $rows->get($currencyId, collect())
                ->sortBy(fn (Price $price): array => [
                    $price->customer_group_id === null ? 0 : 1,
                    $price->min_quantity > 1 ? 1 : 0,
                    $price->min_quantity,
                    $price->customer_group_id ?? 0,
                ])
                ->map(fn (Price $price): array => [
                    'customer_group_id' => $price->customer_group_id,
                    'min_quantity' => (int) $price->min_quantity,
                    'price' => (int) $price->price,
                    'list_price' => $price->list_price === null ? null : (int) $price->list_price,
                ])
                ->values()
                ->all();
        }

        return $values;
    }

    /**
     * Integers or null, in the order given: validation errors name a row by
     * its index, so reordering here would point them at the wrong row.
     */
    public function normalize(array $data): array
    {
        $int = fn (mixed $value): ?int => is_numeric($value) ? (int) $value : null;

        foreach ($data as $code => $rows) {
            $data[$code] = array_values(array_map(fn (array $row): array => [
                'customer_group_id' => $int($row['customer_group_id'] ?? null),
                'min_quantity' => $int($row['min_quantity'] ?? null) ?? 1,
                'price' => $int($row['price'] ?? null),
                'list_price' => $int($row['list_price'] ?? null),
            ], array_filter((array) $rows, 'is_array')));
        }

        return $data;
    }

    public function rules(Model $record): array
    {
        /** @var ProductVariant $record */
        $priced = $record->exists
            ? $record->prices()->whereNull('customer_group_id')->where('min_quantity', 1)->pluck('currency_id')->all()
            : [];

        $rules = [];

        foreach ($this->currencies() as $code => $currencyId) {
            $rules[$code] = ['nullable', 'array', function (string $attribute, mixed $rows, Closure $fail) use ($priced, $currencyId): void {
                $rows = is_array($rows) ? array_filter($rows, 'is_array') : [];
                $tuples = array_map(fn (array $row): string => ($row['customer_group_id'] ?? 0).':'.($row['min_quantity'] ?? 1), $rows);

                if (count($tuples) !== count(array_unique($tuples))) {
                    $fail(__('panel::pricing.error_duplicate_row'));
                }

                // A stored base price may change but not disappear.
                if (in_array($currencyId, $priced, false) && ! in_array('0:1', $tuples, true)) {
                    $fail(__('panel::pricing.error_base_required'));
                }
            }];
            $rules[$code.'.*'] = ['array'];
            $rules[$code.'.*.customer_group_id'] = ['nullable', 'integer', Rule::exists((new CustomerGroup)->getTable(), 'id')];
            $rules[$code.'.*.min_quantity'] = ['required', 'integer', 'min:1'];
            $rules[$code.'.*.price'] = ['required', 'integer', 'min:0'];
            $rules[$code.'.*.list_price'] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function commit(Model $record, array $values): void
    {
        /** @var ProductVariant $record */
        $drafted = array_intersect_key($this->currencies(), $values);

        $prices = $record->prices()->whereNotIn('currency_id', $drafted)->get()
            ->map(fn (Price $price): array => $price->only(['currency_id', 'customer_group_id', 'min_quantity', 'price', 'list_price']))
            ->all();

        foreach ($this->normalize(array_intersect_key($values, $drafted)) as $code => $rows) {
            foreach ($rows as $row) {
                $prices[] = ['currency_id' => $drafted[$code], ...$row];
            }
        }

        $record->syncPrices($prices);
    }

    public function labels(): array
    {
        $labels = [];

        foreach (array_keys($this->currencies()) as $code) {
            $labels[$code] = 'panel::pricing.title';
            $labels[$code.'.*.price'] = 'panel::pricing.amount';
            $labels[$code.'.*.list_price'] = 'panel::pricing.compare_at';
            $labels[$code.'.*.min_quantity'] = 'panel::pricing.tier_min_qty';
            $labels[$code.'.*.customer_group_id'] = 'panel::pricing.group_column';
        }

        return $labels;
    }

    /** @return array<string, int> */
    protected function currencies(): array
    {
        return $this->currencies ??= Currency::query()
            ->where('enabled', true)
            ->orderByDesc('default')
            ->orderBy('code')
            ->pluck('id', 'code')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}

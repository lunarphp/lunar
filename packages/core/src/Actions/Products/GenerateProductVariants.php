<?php

namespace Lunar\Core\Actions\Products;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Products\GeneratesProductVariants;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;

/**
 * Rebuild a product's variant set from an option selection. The diff is
 * computed before anything is written: variants whose value combination
 * survives are kept untouched (pricing, stock and identifiers intact),
 * missing combinations are created with defaults copied from the product's
 * first variant, and orphaned combinations are removed — the whole run is
 * refused up front while any removal carries order history.
 *
 * A variant with no option values (the just-created or simple-shape product)
 * is adopted into the first new combination rather than replaced, so its
 * prices, stock and SKU survive the first generation. An empty selection is
 * the collapse path: options detach and only the first variant remains.
 *
 * Shared options are attached by reference; exclusive options (rows the
 * merchant defines inline on the product) are created, renamed and pruned
 * here as part of the sync.
 *
 * @phpstan-type ValueToken object{id: ?int, name: array<string, string>, model: ?ProductOptionValue}
 *
 * Tokens are objects on purpose: exclusive values get their ids during the
 * apply phase, and the same token instances sit inside every combination.
 */
class GenerateProductVariants implements GeneratesProductVariants
{
    /**
     * Attached options are capped to keep the cartesian product sane — the
     * same ceiling every comparable admin applies.
     */
    public const MAX_OPTIONS = 3;

    public function execute(Product $product, array $selections): array
    {
        if (count($selections) > static::MAX_OPTIONS) {
            throw new ProductActionException(
                sprintf('Products carry at most %d options.', static::MAX_OPTIONS)
            );
        }

        return DB::transaction(function () use ($product, $selections): array {
            if ($selections === []) {
                return $this->collapse($product);
            }

            $resolved = $this->resolveSelections($product, $selections);

            $combos = $this->combos($resolved);

            $variants = $product->variants()->with(['values', 'basePrices'])->orderBy('id')->get();

            [$kept, $added, $removed, $adopted] = $this->diff($combos, $variants);

            $this->guardRemovals($removed);

            $previousExclusiveIds = $product->productOptions()->exclusive()->get()->pluck('id');

            $this->applyOptionMutations($resolved);

            $this->syncAttachedOptions($product, $resolved);

            if ($adopted !== null) {
                [$variant, $combo] = $adopted;
                $variant->values()->sync($this->valueIds($combo));
            }

            foreach ($added as $combo) {
                $this->createVariant($product, $variants->first(), $combo);
            }

            foreach ($removed as $variant) {
                $variant->delete();
            }

            $this->pruneDetachedExclusiveOptions($previousExclusiveIds, $resolved);

            return [
                'kept' => count($kept) + ($adopted ? 1 : 0),
                'added' => count($added),
                'removed' => $removed->count(),
            ];
        });
    }

    /**
     * @return array{kept: int, added: int, removed: int}
     */
    protected function collapse(Product $product): array
    {
        $variants = $product->variants()->orderBy('id')->get();

        /** @var ProductVariant $survivor */
        $survivor = $variants->first();

        $removals = $variants->slice(1)->values();

        $this->guardRemovals($removals);

        foreach ($removals as $variant) {
            $variant->delete();
        }

        $survivor?->values()->detach();

        $exclusive = $product->productOptions()->exclusive()->get();

        $product->productOptions()->detach();

        foreach ($exclusive as $option) {
            $option->delete();
        }

        return ['kept' => $survivor ? 1 : 0, 'added' => 0, 'removed' => $removals->count()];
    }

    /**
     * Resolve every selection into its option model (existing or pending) and
     * ordered value tokens, validating shapes before anything is written.
     *
     * @param  array<int, array<string, mixed>>  $selections
     * @return array<int, array{type: string, option: ?ProductOption, name: array<string, string>, values: array<int, ValueToken>}>
     */
    protected function resolveSelections(Product $product, array $selections): array
    {
        return collect($selections)->map(function (array $selection) use ($product): array {
            $resolved = match ($selection['type'] ?? null) {
                'shared' => $this->resolveShared($selection),
                'exclusive' => $this->resolveExclusive($product, $selection),
                default => throw new ProductActionException('Unknown option selection type.'),
            };

            if ($resolved['values'] === []) {
                throw new ProductActionException('Every attached option needs at least one selected value.');
            }

            return $resolved;
        })->all();
    }

    /**
     * @param  array<string, mixed>  $selection
     * @return array{type: string, option: ProductOption, name: array<string, string>, values: array<int, ValueToken>}
     */
    protected function resolveShared(array $selection): array
    {
        /** @var ProductOption $option */
        $option = ProductOption::query()->shared()->find($selection['id'] ?? null);

        if (! $option) {
            throw new ProductActionException('Shared option not found.');
        }

        $valueIds = collect($selection['value_ids'] ?? [])->map(fn ($id) => (int) $id);

        $values = $option->values()->orderBy('position')->get()->filter(
            fn (ProductOptionValue $value) => $valueIds->contains($value->id)
        );

        if ($values->count() !== $valueIds->unique()->count()) {
            throw new ProductActionException('Selected values must belong to the shared option.');
        }

        return [
            'type' => 'shared',
            'option' => $option,
            'name' => $this->translatedName($option->name),
            'values' => $values->map(fn (ProductOptionValue $value): object => (object) [
                'id' => $value->id,
                'name' => $this->translatedName($value->name),
                'model' => $value,
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $selection
     * @return array{type: string, option: ?ProductOption, name: array<string, string>, values: array<int, ValueToken>}
     */
    protected function resolveExclusive(Product $product, array $selection): array
    {
        $option = null;

        if (! empty($selection['id'])) {
            /** @var ?ProductOption $option */
            $option = $product->productOptions()->exclusive()->find($selection['id']);

            if (! $option) {
                throw new ProductActionException('Exclusive option not found on this product.');
            }
        }

        $name = $this->translatedName($selection['name'] ?? null);

        if ($name === []) {
            throw new ProductActionException('Exclusive options need a name.');
        }

        $values = collect($selection['values'] ?? [])->map(function ($value) use ($option): object {
            $id = is_array($value) ? ($value['id'] ?? null) : null;
            $model = null;

            if ($id !== null) {
                $model = $option?->values()->find($id);

                if (! $model) {
                    throw new ProductActionException('Exclusive option values must belong to the option.');
                }
            }

            $valueName = $this->translatedName(is_array($value) ? ($value['name'] ?? null) : $value);

            if ($valueName === []) {
                throw new ProductActionException('Exclusive option values need a name.');
            }

            return (object) ['id' => $id, 'name' => $valueName, 'model' => $model];
        })->values()->all();

        return [
            'type' => 'exclusive',
            'option' => $option,
            'name' => $name,
            'values' => $values,
        ];
    }

    /**
     * Cartesian product of the resolved selections, respecting option order.
     *
     * @param  array<int, array{values: array<int, ValueToken>}>  $resolved
     * @return array<int, array<int, ValueToken>>
     */
    protected function combos(array $resolved): array
    {
        $combos = [[]];

        foreach ($resolved as $selection) {
            $next = [];

            foreach ($combos as $combo) {
                foreach ($selection['values'] as $value) {
                    $next[] = [...$combo, $value];
                }
            }

            $combos = $next;
        }

        return $combos;
    }

    /**
     * Split combinations and existing variants into keep / add / remove, with
     * at most one valueless variant adopted into the first new combination.
     *
     * @param  array<int, array<int, ValueToken>>  $combos
     * @param  Collection<int, ProductVariant>  $variants
     * @return array{0: array<int, array<int, ValueToken>>, 1: array<int, array<int, ValueToken>>, 2: Collection<int, ProductVariant>, 3: ?array{0: ProductVariant, 1: array<int, ValueToken>}}
     */
    protected function diff(array $combos, $variants): array
    {
        $signatureOf = fn (array $ids): string => collect($ids)->sort()->implode(':');

        $bySignature = $variants
            ->filter(fn (ProductVariant $variant) => $variant->values->isNotEmpty())
            ->keyBy(fn (ProductVariant $variant) => $signatureOf($variant->values->pluck('id')->all()));

        $kept = [];
        $added = [];
        $matched = [];

        foreach ($combos as $combo) {
            $ids = $this->valueIds($combo, strict: false);

            $signature = count($ids) === count($combo) ? $signatureOf($ids) : null;

            if ($signature !== null && $bySignature->has($signature)) {
                $kept[] = $combo;
                $matched[$signature] = true;

                continue;
            }

            $added[] = $combo;
        }

        $removed = $bySignature
            ->reject(fn (ProductVariant $variant, string $signature) => isset($matched[$signature]))
            ->values();

        $valueless = $variants->filter(fn (ProductVariant $variant) => $variant->values->isEmpty())->values();

        $adopted = null;

        if ($valueless->isNotEmpty() && $added !== []) {
            $adopted = [$valueless->shift(), array_shift($added)];
        }

        $removed = $removed->concat($valueless)->values();

        return [$kept, $added, $removed, $adopted];
    }

    /**
     * @param  Collection<int, ProductVariant>  $removals
     */
    protected function guardRemovals($removals): void
    {
        foreach ($removals as $variant) {
            if ($variant->hasOrderHistory()) {
                throw new ProductActionException(
                    'Some variants being removed have order history — adjust the option selection to keep them.'
                );
            }
        }
    }

    /**
     * Create pending exclusive options and values, rename existing ones, and
     * drop exclusive values missing from the selection. Tokens gain their
     * models/ids here so combinations can attach them.
     *
     * @param  array<int, array{type: string, option: ?ProductOption, name: array<string, string>, values: array<int, ValueToken>}>  $resolved
     */
    protected function applyOptionMutations(array &$resolved): void
    {
        foreach ($resolved as &$selection) {
            if ($selection['type'] !== 'exclusive') {
                continue;
            }

            $option = $selection['option'];

            if ($option) {
                $option->update(['name' => $selection['name']]);
            } else {
                $option = ProductOption::create([
                    'name' => $selection['name'],
                    'shared' => false,
                ]);

                $selection['option'] = $option;
            }

            $keptValueIds = [];

            foreach ($selection['values'] as $position => $value) {
                if ($value->model) {
                    $value->model->update([
                        'name' => $value->name,
                        'position' => ($position + 1) * 10,
                    ]);
                } else {
                    $value->model = $option->values()->create([
                        'name' => $value->name,
                        'position' => ($position + 1) * 10,
                    ]);
                    $value->id = $value->model->id;
                }

                $keptValueIds[] = $value->model->id;
            }

            $option->values()
                ->whereKeyNot($keptValueIds)
                ->get()
                ->each(fn (ProductOptionValue $stale) => $stale->delete());
        }
        unset($selection);
    }

    /**
     * @param  array<int, array{option: ?ProductOption}>  $resolved
     */
    protected function syncAttachedOptions(Product $product, array $resolved): void
    {
        $sync = [];

        foreach ($resolved as $position => $selection) {
            $sync[$selection['option']->id] = ['position' => ($position + 1) * 10];
        }

        $product->productOptions()->sync($sync);
    }

    protected function createVariant(Product $product, ?ProductVariant $template, array $combo): ProductVariant
    {
        /** @var ProductVariant $variant */
        $variant = $product->variants()->create([
            'tax_class_id' => $template?->tax_class_id ?? TaxClass::getDefault()->id,
            'shippable' => $template?->shippable ?? true,
            'selling_policy' => $template?->selling_policy ?? 'always',
            'unit_quantity' => $template?->unit_quantity ?? 1,
            'min_quantity' => $template?->min_quantity ?? 1,
            'quantity_increment' => $template?->quantity_increment ?? 1,
            'sku' => $this->suggestSku($product, $combo),
        ]);

        $variant->values()->attach($this->valueIds($combo));

        foreach ($template?->basePrices ?? [] as $price) {
            /** @var Price $copy */
            $copy = $price->replicate();
            $copy->priceable_type = $variant->getMorphClass();
            $copy->priceable_id = $variant->id;
            $copy->save();
        }

        return $variant;
    }

    /**
     * Suggest a unique SKU from the product's default URL slug plus the
     * combination's value names: SLUG-VALUE-VALUE, SLUG-VALUE-VALUE-2, ...
     *
     * @param  array<int, ValueToken>  $combo
     */
    protected function suggestSku(Product $product, array $combo): string
    {
        $base = $product->defaultUrl()->first()?->slug
            ?: Str::slug((string) $product->translate('name'))
            ?: 'variant';

        $parts = collect($combo)
            ->map(fn (object $value) => Str::slug($this->defaultLocaleName($value->name)))
            ->filter()
            ->all();

        $sku = strtoupper(implode('-', [$base, ...$parts]));

        $candidate = $sku;

        for ($suffix = 2; ProductVariant::query()->where('sku', $candidate)->exists(); $suffix++) {
            $candidate = $sku.'-'.$suffix;
        }

        return $candidate;
    }

    /**
     * Exclusive options that were attached to this product but are absent
     * from the new selection belong to nobody else — remove them with their
     * values. Kept variants cannot reference them: a variant carrying a
     * dropped option's value never matches a surviving combination.
     *
     * @param  Collection<int, int>  $previousExclusiveIds
     * @param  array<int, array{option: ?ProductOption}>  $resolved
     */
    protected function pruneDetachedExclusiveOptions($previousExclusiveIds, array $resolved): void
    {
        $keptIds = collect($resolved)->map(fn (array $selection) => $selection['option']->id);

        ProductOption::query()
            ->exclusive()
            ->whereKey($previousExclusiveIds->diff($keptIds))
            ->get()
            ->each(fn (ProductOption $option) => $option->delete());
    }

    /**
     * @param  array<int, ValueToken>  $combo
     * @return array<int, int>
     */
    protected function valueIds(array $combo, bool $strict = true): array
    {
        $ids = collect($combo)->pluck('id')->filter()->values()->all();

        if ($strict && count($ids) !== count($combo)) {
            throw new ProductActionException('Unresolved option values in combination.');
        }

        return $ids;
    }

    /**
     * Normalise a translated (or plain) name into a locale-keyed map.
     *
     * @return array<string, string>
     */
    protected function translatedName(mixed $name): array
    {
        if (is_string($name)) {
            $name = [Language::getDefault()->code => $name];
        }

        return collect($name ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->all();
    }

    protected function defaultLocaleName(array $name): string
    {
        return $name[Language::getDefault()->code] ?? (string) collect($name)->first();
    }
}

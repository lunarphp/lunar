<?php

namespace Lunar\Core\Actions\Products;

use Illuminate\Support\Str;
use Lunar\Core\Contracts\Actions\Products\MapsVariantsToProductOptions;
use Lunar\Core\Utils\Arr;

/**
 * Compute the variant-permutation table for a product given its current
 * option-value selection and existing variants. The result is what the
 * Filament "product options" widget renders; consumers can call it from
 * any UI/CLI/API surface.
 *
 * @phpstan-type Variant array{id?: int|null, sku?: string|null, price?: float|int, stock?: int, values: array<string, string>}
 * @phpstan-type VariantPermutation array{key: string, variant_id: int|null, copied_id: int|null, sku: string|null, price: float|int, stock: int, values: array<string, string>}
 */
class MapVariantsToProductOptions implements MapsVariantsToProductOptions
{
    /**
     * @param  array<string, array<int, string>>  $options
     * @param  array<int, Variant>  $variants
     * @return array<int, VariantPermutation>
     */
    public function execute(array $options, array $variants, bool $fillMissing = true): array
    {
        $permutations = Arr::permutate($options);

        if (count($options) === 1) {
            $newPermutations = [];

            foreach ($permutations as $p) {
                $newPermutations[] = [
                    array_key_first($options) => $p,
                ];
            }

            $permutations = $newPermutations;
        }

        $variantPermutations = [];

        foreach ($permutations as $permutation) {
            $variantIndex = collect($variants)->search(function ($variant) use ($permutation) {
                $valueDifference = array_diff_assoc($permutation, $variant['values']);

                if (! count($valueDifference)) {
                    return $variant;
                }

                $amountMatched = count($permutation) - count($valueDifference);

                return $amountMatched === count($variant['values']);
            });

            $variant = $variants[$variantIndex] ?? null;

            $variantId = $variant['id'] ?? null;
            $sku = $variant['sku'] ?? null;
            $copiedFrom = null;
            $shouldFill = true;

            if ($variant) {
                $existing = collect($variantPermutations)
                    ->first(fn ($p) => $p['variant_id'] === $variant['id']);

                if ($existing) {
                    $diff = array_diff_assoc($permutation, $variant['values']);
                    $sku = $existing['sku'].'-'.implode('-', array_values($diff));
                    $variantId = null;
                    $copiedFrom = $variant['id'];
                }

                if ($existing && ! $fillMissing) {
                    $shouldFill = false;
                }
            }

            if ($shouldFill) {
                $variantPermutations[] = [
                    'key' => Str::random(),
                    'variant_id' => $variantId,
                    'copied_id' => $copiedFrom,
                    'sku' => $sku,
                    'price' => $variant['price'] ?? 0,
                    'stock' => $variant['stock'] ?? 0,
                    'values' => $permutation,
                ];
            }
        }

        return $variantPermutations;
    }

    /**
     * Convenience static wrapper retained for backwards compatibility with
     * the legacy bridge utility shape (`MapVariantsToProductOptions::map(...)`).
     *
     * @param  array<string, array<int, string>>  $options
     * @param  array<int, Variant>  $variants
     * @return array<int, VariantPermutation>
     */
    public static function map(array $options, array $variants, bool $fillMissing = true): array
    {
        return app(MapsVariantsToProductOptions::class)->execute($options, $variants, $fillMissing);
    }
}

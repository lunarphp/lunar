<?php

namespace Lunar\Admin\Support\Actions\Products;

use Illuminate\Support\Str;
use Lunar\Utils\Arr;

class MapVariantsToProductOptions
{
    public static function map(array $options, array $variants, bool $fillMissing = true): array
    {
        $permutations = Arr::permutate($options);

        if (count($options) == 1) {
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

                return $amountMatched == count($variant['values']);
            });

            $variant = $variants[$variantIndex] ?? null;

            $variantId = $variant['id'] ?? null;
            $sku = $variant['sku'] ?? null;
            $copiedFrom = null;
            $shouldFill = true;

            if ($variant) {
                // Does this variant already exist in our permutations?
                // if so we want to mark it as new but
                $existing = collect($variantPermutations)
                    ->first(
                        fn ($p) => $p['variant_id'] == $variant['id']
                    );

                // Now what?
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
}

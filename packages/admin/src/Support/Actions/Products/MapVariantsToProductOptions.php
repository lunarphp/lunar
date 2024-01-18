<?php

namespace Lunar\Admin\Support\Actions\Products;

use Lunar\Utils\Arr;

class MapVariantsToProductOptions
{
    public static function map(array $options, array $variants): array
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

                $valueDifference = array_diff($permutation, $variant['values']);

                if (! $valueDifference) {
                    return $variant;
                }

                dd($valueDifference, $permutation, $variant['values']);

                $diffCount = count();
                $amountMatched = count($permutation) - $diffCount;

                return ! $diffCount || $amountMatched == count($variant['values']);
            });

            $variant = $variants[$variantIndex]['model'] ?? null;

            $variantPermutations[] = [
                'variant_id' => $variant?->id,
                'sku' => $variant?->sku,
                'values' => $permutation,
            ];

            if (! is_null($variantIndex)) {
                $variants->forget($variantIndex);
            }
        }

        return [];
    }
}

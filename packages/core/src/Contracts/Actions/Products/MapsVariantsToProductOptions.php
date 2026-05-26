<?php

namespace Lunar\Core\Contracts\Actions\Products;

/**
 * @phpstan-type Variant array{id?: int|null, sku?: string|null, price?: float|int, stock?: int, values: array<string, string>}
 * @phpstan-type VariantPermutation array{key: string, variant_id: int|null, copied_id: int|null, sku: string|null, price: float|int, stock: int, values: array<string, string>}
 */
interface MapsVariantsToProductOptions
{
    /**
     * @param  array<string, array<int, string>>  $options
     * @param  array<int, Variant>  $variants
     * @return array<int, VariantPermutation>
     */
    public function execute(array $options, array $variants, bool $fillMissing = true): array;
}

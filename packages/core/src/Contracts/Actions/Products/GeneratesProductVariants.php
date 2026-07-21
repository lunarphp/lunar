<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Product;

interface GeneratesProductVariants
{
    /**
     * Sync the product's attached options to the given selections and rebuild
     * its variant set from the resulting value combinations, keeping variants
     * whose combination survives.
     *
     * Each selection is either a shared option reference or an exclusive
     * (product-local) option definition, in display order:
     *
     * - shared: `{type: 'shared', id: int, value_ids: int[]}`
     * - exclusive: `{type: 'exclusive', id: ?int, name: array<string, string>|string, values: array{id: ?int, name: array<string, string>|string}[]}`
     *
     * An empty selection collapses the product to a single variant.
     *
     * @param  array<int, array<string, mixed>>  $selections
     * @return array{kept: int, added: int, removed: int}
     */
    public function execute(Product $product, array $selections): array;
}

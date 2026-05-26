<?php

namespace Lunar\Core\Contracts\Actions\Products;

use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;

interface AdjustsStock
{
    public function execute(ProductVariantContract $variant, int $delta, ?string $reason = null): ProductVariant;
}

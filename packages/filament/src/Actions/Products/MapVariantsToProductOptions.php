<?php

namespace Lunar\Filament\Actions\Products;

use Lunar\Core\Actions\Products\MapVariantsToProductOptions as CoreMapVariantsToProductOptions;

/**
 * @deprecated since v2 — use `Lunar\Core\Actions\Products\MapVariantsToProductOptions`.
 *             This shim is kept so existing call sites
 *             (`Lunar\Filament\Actions\Products\MapVariantsToProductOptions::map(...)`)
 *             keep working. It will be removed in v3.
 */
class MapVariantsToProductOptions
{
    /**
     * @param  array<string, array<int, string>>  $options
     * @param  array<int, array<string, mixed>>  $variants
     * @return array<int, array<string, mixed>>
     */
    public static function map(array $options, array $variants, bool $fillMissing = true): array
    {
        return CoreMapVariantsToProductOptions::map($options, $variants, $fillMissing);
    }
}

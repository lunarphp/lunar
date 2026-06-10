<?php

namespace Lunar\Checkout\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Checkout\Contracts\CheckoutAssets as CheckoutAssetsContract;

/**
 * @method static void register(string $package, string $source, string $entry = 'checkout.js', ?string $compat = null)
 * @method static array<int, array{package: string, url: string, compat: string|null}> all()
 * @method static string|null path(string $package, string $file)
 *
 * @see \Lunar\Checkout\Support\CheckoutAssets
 */
class CheckoutAssets extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CheckoutAssetsContract::class;
    }
}

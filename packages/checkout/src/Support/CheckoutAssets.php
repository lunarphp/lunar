<?php

namespace Lunar\Checkout\Support;

use Lunar\Checkout\Contracts\CheckoutAssets as CheckoutAssetsContract;

/**
 * Registry of contributed element/gateway chunks (spec 0009).
 *
 * A thin store of Vite configs, one per contributing package — the checkout
 * root view renders each with Laravel's own Vite class, which already handles
 * the dev (hot file → dev server + HMR) vs build (manifest → hashed files)
 * switch. This is exactly Statamic's addon model (Statamic::vite() +
 * availableVites()); we add nothing on top.
 *
 * Bound as a singleton in the service provider's register() and populated from
 * other providers in boot() — registration is static, never per-request
 * (Octane-safe), same rule as the element registry.
 */
class CheckoutAssets implements CheckoutAssetsContract
{
    /** @var array<string, array{buildDirectory: string, hotFile: string, input: array<int, string>}> */
    private array $packages = [];

    public function register(string $package, array $config): void
    {
        $this->packages[$package] = array_merge([
            // Public build dir (relative to public/) the chunk was built into,
            // its dev hot file, and the entry point(s) to load. Defaults follow
            // Laravel Vite; the hot file is namespaced so it never collides with
            // the host app's own `public/hot`.
            'buildDirectory' => 'build',
            'hotFile' => public_path($package.'.hot'),
            'input' => [],
        ], $config);
    }

    public function all(): array
    {
        return $this->packages;
    }
}

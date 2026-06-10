<?php

namespace Lunar\Checkout\Support;

use Lunar\Checkout\Contracts\CheckoutAssets as CheckoutAssetsContract;

/**
 * Build-time registry of contributed element/gateway chunks (spec 0009).
 *
 * Bound as a singleton in the service provider's register() and populated from
 * other providers — same Octane-safe rule as the element registry: registration
 * is static, never per-request. Mirrors Statamic's Statamic::script()/registerVite
 * "publish + register in one call" ergonomics.
 */
class CheckoutAssets implements CheckoutAssetsContract
{
    /** @var array<string, array{source: string, entry: string, compat: string|null}> */
    private array $packages = [];

    public function register(string $package, string $source, string $entry = 'checkout.js', ?string $compat = null): void
    {
        $this->packages[$package] = [
            'source' => rtrim($source, '/'),
            'entry' => $entry,
            'compat' => $compat,
        ];
    }

    public function all(): array
    {
        return array_values(array_map(fn (array $meta, string $package): array => [
            'package' => $package,
            // Default to the same-origin asset route (spec 0009 §C.1) — no
            // vendor:publish required. A merchant who publishes to public/ can
            // front it with a CDN instead; the route stays the no-publish default.
            'url' => route('lunar.checkout.assets', [$package, $meta['entry']]),
            'compat' => $meta['compat'],
        ], $this->packages, array_keys($this->packages)));
    }

    public function path(string $package, string $file): ?string
    {
        if (! isset($this->packages[$package])) {
            return null;
        }

        $base = realpath($this->packages[$package]['source']);

        if ($base === false) {
            return null;
        }

        $full = realpath($base.'/'.$file);

        // Refuse anything that resolves outside the registered source dir.
        if ($full === false || ! str_starts_with($full, $base.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $full;
    }
}

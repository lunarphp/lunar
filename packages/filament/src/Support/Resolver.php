<?php

namespace Lunar\Filament\Support;

/**
 * Resolves the runtime class for a bridge schema, table, or relation manager.
 *
 * When `lunar-filament.resolver.prefer_published` is true (the default) and a
 * subclass exists in the consumer app under the configured `app_namespace`,
 * the published copy is returned instead of the bridge class.
 *
 * Publishing is a one-way door: the published copy no longer receives bridge
 * improvements on subsequent updates. For additive extension prefer hooks via
 * \Lunar\Filament\Support\Facades\LunarFilament::extensions().
 */
class Resolver
{
    /**
     * @template T
     *
     * @param  class-string<T>  $bridgeClass  Fully-qualified bridge class name
     * @return class-string<T>
     */
    public static function resolve(string $bridgeClass): string
    {
        if (! config('lunar-filament.resolver.prefer_published', true)) {
            return $bridgeClass;
        }

        $appNamespace = (string) config('lunar-filament.resolver.app_namespace', 'App\\Filament');

        $relative = static::relativePath($bridgeClass);

        if ($relative === null) {
            return $bridgeClass;
        }

        $candidate = $appNamespace.'\\'.$relative;

        if (class_exists($candidate) && is_subclass_of($candidate, $bridgeClass)) {
            return $candidate;
        }

        return $bridgeClass;
    }

    protected static function relativePath(string $bridgeClass): ?string
    {
        $prefix = 'Lunar\\Filament\\';

        if (! str_starts_with($bridgeClass, $prefix)) {
            return null;
        }

        return substr($bridgeClass, strlen($prefix));
    }
}

<?php

namespace Lunar\Filament\Support;

use Filament\Schemas\Schema;
use Filament\Tables\Table;

/**
 * Resolves the runtime class for a bridge schema, table, or relation manager.
 *
 * When `lunar.filament.resolver.prefer_published` is true (the default) and a
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
     * Configure a Filament schema via a bridge form class, preferring the
     * consumer's published subclass when one is present.
     */
    public static function form(string $bridgeFormClass, Schema $schema): Schema
    {
        $class = static::resolve($bridgeFormClass);

        return $class::configure($schema);
    }

    /**
     * Configure a Filament table via a bridge table class, preferring the
     * consumer's published subclass when one is present.
     */
    public static function table(string $bridgeTableClass, Table $table): Table
    {
        $class = static::resolve($bridgeTableClass);

        return $class::configure($table);
    }

    /**
     * Configure a Filament infolist schema via a bridge infolist class,
     * preferring the consumer's published subclass when one is present.
     */
    public static function infolist(string $bridgeInfolistClass, Schema $schema): Schema
    {
        $class = static::resolve($bridgeInfolistClass);

        return $class::configure($schema);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $bridgeClass  Fully-qualified bridge class name
     * @return class-string<T>
     */
    public static function resolve(string $bridgeClass): string
    {
        if (! config('lunar.filament.resolver.prefer_published', true)) {
            return $bridgeClass;
        }

        $appNamespace = (string) config('lunar.filament.resolver.app_namespace', 'App\\Filament');

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

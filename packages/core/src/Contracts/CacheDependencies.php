<?php

namespace Lunar\Core\Contracts;

use Closure;

/**
 * Registry of named cache-dependency graphs. A graph declares what a page
 * composed from a root entity depends on; the default graph for an entity is
 * named after its morph alias (`product`, `collection`, ...). Page composition
 * is store-specific, so this is a registry, not a model method.
 */
interface CacheDependencies
{
    /**
     * Register (or override) a named dependency graph. The definition is either
     * a list of relation paths walked from the root, or a closure returning the
     * cache-participating models (or tags) the page depends on.
     *
     * @param  array<int, string>|Closure  $definition
     */
    public function define(string $name, array|Closure $definition): void;

    /**
     * @return array<int, string>|Closure|null
     */
    public function get(string $name): array|Closure|null;

    public function has(string $name): bool;

    /** Clear the registry (test isolation). */
    public function flush(): void;
}

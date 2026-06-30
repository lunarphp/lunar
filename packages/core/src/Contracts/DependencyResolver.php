<?php

namespace Lunar\Core\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Resolves the deduped cache-tag set a page composed from a root entity depends
 * on, by walking a registered dependency graph (see {@see CacheDependencies}).
 * This is the read counterpart to the invalidation events: a storefront attaches
 * the returned tags when it caches the page, and invalidates by tag when an event
 * carrying one of them fires.
 */
interface DependencyResolver
{
    /**
     * The graph defaults to the one named after the model's morph alias; an
     * unregistered graph resolves to the root's own tags.
     *
     * @return array<int, string>
     */
    public function for(Model $root, ?string $graph = null): array;
}

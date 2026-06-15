<?php

namespace Lunar\Core\Contracts;

use Illuminate\Support\Collection;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\States\Fulfilment\FulfilmentState;

/**
 * The registry of fulfilment methods, mirroring {@see CarrierManifest}. Seeded
 * on construction with the core three; a consumer registers their own from a
 * service provider. It also assembles the union of every registered method's
 * states and transitions (so Spatie can cast every flow's states) and the
 * category map the order rollup queries.
 */
interface FulfilmentMethodManifest
{
    /**
     * Register a fulfilment method. Accepts a method instance or the class name
     * of a method (resolved from the container).
     *
     * @param  FulfilmentMethod|class-string<FulfilmentMethod>  $method
     * @return self
     */
    public function register(FulfilmentMethod|string $method);

    /**
     * Get all registered methods, keyed by key, in priority order (ascending).
     *
     * @return Collection<string, FulfilmentMethod>
     */
    public function all(): Collection;

    /**
     * Get a single method by key, or null when it is not registered.
     */
    public function get(?string $key): ?FulfilmentMethod;

    /**
     * The union of every registered method's states.
     *
     * @return array<class-string<FulfilmentState>>
     */
    public function states(): array;

    /**
     * The merged union of every registered method's transitions.
     *
     * @return array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>>
     */
    public function transitions(): array;

    /**
     * The `$name`s of every registered state in the given category — used by
     * the order rollup to ask "which states count as fulfilled / returned?".
     *
     * @return list<string>
     */
    public function stateNamesIn(FulfilmentStateCategory $category): array;
}

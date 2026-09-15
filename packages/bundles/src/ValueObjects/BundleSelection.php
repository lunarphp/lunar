<?php

namespace Lunar\Bundles\ValueObjects;

use Countable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Lunar\Core\Models\ProductVariant;
use Traversable;

/**
 * The customer's resolved list of components for one cart line.
 *
 * @implements IteratorAggregate<int, SelectedComponent>
 */
final readonly class BundleSelection implements Countable, IteratorAggregate
{
    /**
     * @param  Collection<int, SelectedComponent>  $components
     * @param  bool  $default  whether this is exactly the bundle's default selection
     */
    public function __construct(
        public Collection $components,
        private bool $default,
    ) {}

    public function isDefault(): bool
    {
        return $this->default;
    }

    /** @return Collection<int, ProductVariant> */
    public function variants(): Collection
    {
        return $this->components->map(fn (SelectedComponent $component) => $component->variant);
    }

    /**
     * @param  callable(SelectedComponent): mixed  $callback
     */
    public function each(callable $callback): self
    {
        $this->components->each($callback);

        return $this;
    }

    /**
     * @template T
     *
     * @param  callable(SelectedComponent): T  $callback
     * @return Collection<int, T>
     */
    public function map(callable $callback): Collection
    {
        return $this->components->map($callback);
    }

    public function isEmpty(): bool
    {
        return $this->components->isEmpty();
    }

    public function count(): int
    {
        return $this->components->count();
    }

    public function getIterator(): Traversable
    {
        return $this->components->getIterator();
    }
}

<?php

namespace Lunar\Core\Manifests;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Contracts\FulfilmentMethodManifest as FulfilmentMethodManifestContract;
use Lunar\Core\Drivers\FulfilmentMethods\Collection as CollectionMethod;
use Lunar\Core\Drivers\FulfilmentMethods\Digital;
use Lunar\Core\Drivers\FulfilmentMethods\Shipping;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\States\Fulfilment\FulfilmentState;
use ReflectionClass;

class FulfilmentMethodManifest implements FulfilmentMethodManifestContract
{
    /**
     * The registered methods, keyed by method key.
     *
     * @var Collection<string, FulfilmentMethod>
     */
    protected Collection $methods;

    /**
     * Memoised union of every method's states.
     *
     * @var array<class-string<FulfilmentState>>|null
     */
    protected ?array $states = null;

    /**
     * Memoised merged union of every method's transitions.
     *
     * @var array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>>|null
     */
    protected ?array $transitions = null;

    /**
     * Memoised state-name => category map across every registered method.
     *
     * @var array<string, FulfilmentStateCategory>|null
     */
    protected ?array $categories = null;

    public function __construct()
    {
        $this->methods = collect();

        $this->registerCoreMethods();
    }

    /**
     * {@inheritDoc}
     */
    public function register(FulfilmentMethod|string $method)
    {
        $method = $this->resolve($method);

        $this->methods->put($method->getKey(), $method);

        $this->flush();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return $this->methods->sortBy(fn (FulfilmentMethod $method) => $method->priority());
    }

    /**
     * {@inheritDoc}
     */
    public function get(?string $key): ?FulfilmentMethod
    {
        if ($key === null) {
            return null;
        }

        return $this->methods->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function states(): array
    {
        return $this->states ??= $this->all()
            ->flatMap(fn (FulfilmentMethod $method) => $method->states())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * {@inheritDoc}
     */
    public function transitions(): array
    {
        if ($this->transitions !== null) {
            return $this->transitions;
        }

        $merged = [];

        foreach ($this->all() as $method) {
            foreach ($method->transitions() as $from => $tos) {
                $merged[$from] = array_values(array_unique(array_merge($merged[$from] ?? [], $tos)));
            }
        }

        return $this->transitions = $merged;
    }

    /**
     * {@inheritDoc}
     */
    public function stateNamesIn(FulfilmentStateCategory $category): array
    {
        return collect($this->categoryMap())
            ->filter(fn (FulfilmentStateCategory $stateCategory) => $stateCategory === $category)
            ->keys()
            ->all();
    }

    /**
     * The state `$name` => category map across every registered method's
     * states. Built without constructing the states (so it never re-enters the
     * Spatie config build), reading the category off a constructor-less instance.
     *
     * @return array<string, FulfilmentStateCategory>
     */
    protected function categoryMap(): array
    {
        return $this->categories ??= collect($this->states())
            ->mapWithKeys(function (string $stateClass) {
                /** @var FulfilmentState $state */
                $state = (new ReflectionClass($stateClass))->newInstanceWithoutConstructor();

                return [$stateClass::$name => $state->category()];
            })
            ->all();
    }

    /**
     * Drop the memoised union so it rebuilds after a method is registered.
     */
    protected function flush(): void
    {
        $this->states = null;
        $this->transitions = null;
        $this->categories = null;
    }

    /**
     * Register the batteries-included core methods. A consumer overrides one by
     * re-registering it from their own service provider.
     */
    protected function registerCoreMethods(): void
    {
        $this->register(app(Digital::class));
        $this->register(app(CollectionMethod::class));
        $this->register(app(Shipping::class));
    }

    /**
     * Normalise a registration into a method instance.
     *
     * @param  FulfilmentMethod|class-string<FulfilmentMethod>  $method
     */
    protected function resolve(FulfilmentMethod|string $method): FulfilmentMethod
    {
        if ($method instanceof FulfilmentMethod) {
            return $method;
        }

        return app($method);
    }
}

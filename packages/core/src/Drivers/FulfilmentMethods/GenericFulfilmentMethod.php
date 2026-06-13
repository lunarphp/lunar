<?php

namespace Lunar\Core\Drivers\FulfilmentMethods;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Fulfilment\FulfilmentState;
use Lunar\Core\States\Fulfilment\Pending;
use Lunar\Core\States\Fulfilment\Shipped;

/**
 * A config-driven fulfilment method. Lets a data-shaped flow (key, label,
 * states, transitions, default/fulfilled state, priority, tracking) be declared
 * entirely in `config('lunar.fulfilment.methods')` without writing a class.
 *
 * Line claiming needs real logic (which products are prescriptions?), so a
 * config method claims nothing by default — a method that auto-assigns lines
 * implements {@see FulfilmentMethod} directly and registers in a service
 * provider (container-for-behaviour, config-for-data).
 */
class GenericFulfilmentMethod implements FulfilmentMethod
{
    /**
     * @param  list<class-string<FulfilmentState>>  $states
     * @param  array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>>  $transitions
     * @param  class-string<FulfilmentState>  $defaultState
     * @param  class-string<FulfilmentState>  $fulfilledState
     */
    public function __construct(
        protected string $key,
        protected string $label,
        protected array $states,
        protected array $transitions,
        protected string $defaultState,
        protected string $fulfilledState,
        protected int $priority = 50,
        protected bool $usesTracking = false,
    ) {}

    /**
     * Build a method from a config array shape.
     *
     * @param  array{label?: string, states?: list<class-string<FulfilmentState>>, transitions?: array<class-string<FulfilmentState>, list<class-string<FulfilmentState>>>, default_state?: class-string<FulfilmentState>, fulfilled_state?: class-string<FulfilmentState>, priority?: int, uses_tracking?: bool}  $config
     */
    public static function fromConfig(string $key, array $config): self
    {
        return new self(
            key: $key,
            label: $config['label'] ?? $key,
            states: $config['states'] ?? [],
            transitions: $config['transitions'] ?? [],
            defaultState: $config['default_state'] ?? Pending::class,
            fulfilledState: $config['fulfilled_state'] ?? Shipped::class,
            priority: $config['priority'] ?? 50,
            usesTracking: $config['uses_tracking'] ?? false,
        );
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return __($this->label);
    }

    /**
     * {@inheritDoc}
     */
    public function states(): array
    {
        return $this->states;
    }

    /**
     * {@inheritDoc}
     */
    public function transitions(): array
    {
        return $this->transitions;
    }

    public function defaultState(): string
    {
        return $this->defaultState;
    }

    public function fulfilledState(): string
    {
        return $this->fulfilledState;
    }

    /**
     * {@inheritDoc}
     */
    public function claim(Order $order, Collection $unclaimed): Collection
    {
        return $unclaimed->take(0);
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function usesTracking(): bool
    {
        return $this->usesTracking;
    }
}

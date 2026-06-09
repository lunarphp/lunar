<?php

namespace Lunar\Checkout;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Lunar\Checkout\Contracts\CheckoutElement;
use Lunar\Checkout\Contracts\ElementRegistry as ElementRegistryContract;

/**
 * Holds the registered checkout elements. The spec 0001 registry adds a fluent
 * placement builder (region/before/after, topological ordering); this prototype
 * keeps registration order and resolves region from the element itself. A
 * consumer registers via the Checkout facade in a service provider's boot().
 */
class ElementRegistry implements ElementRegistryContract
{
    /** @var array<int, class-string<CheckoutElement>|CheckoutElement> */
    private array $elements = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    public function add(string|CheckoutElement $element): static
    {
        $this->elements[] = $element;

        return $this;
    }

    public function all(): array
    {
        $resolved = [];

        foreach ($this->elements as $element) {
            $instance = $this->resolve($element);
            $handle = $instance->handle();

            if (isset($resolved[$handle])) {
                throw new InvalidArgumentException("Duplicate checkout element handle [{$handle}].");
            }

            $resolved[$handle] = $instance;
        }

        return array_values($resolved);
    }

    public function get(string $handle): ?CheckoutElement
    {
        foreach ($this->all() as $element) {
            if ($element->handle() === $handle) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @param  class-string<CheckoutElement>|CheckoutElement  $element
     */
    private function resolve(string|CheckoutElement $element): CheckoutElement
    {
        return is_string($element) ? $this->container->make($element) : $element;
    }
}

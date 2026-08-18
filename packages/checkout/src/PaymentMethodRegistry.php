<?php

namespace Lunar\Checkout;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Lunar\Checkout\Contracts\PaymentMethod;
use Lunar\Checkout\Contracts\PaymentMethodRegistry as PaymentMethodRegistryContract;

/**
 * Holds the payment methods a store has enabled (spec 0002 §B). Core ships
 * none — a gateway package or the host app registers methods in a provider's
 * boot(). With nothing registered the payment region projects empty and the
 * checkout reports not-placeable; core stays gateway-free.
 */
class PaymentMethodRegistry implements PaymentMethodRegistryContract
{
    /** @var array<int, class-string<PaymentMethod>|PaymentMethod> */
    private array $methods = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    public function add(string|PaymentMethod $method): static
    {
        $this->methods[] = $method;

        return $this;
    }

    public function all(): array
    {
        $resolved = [];

        foreach ($this->methods as $method) {
            $instance = $this->resolve($method);
            $handle = $instance->handle();

            if (isset($resolved[$handle])) {
                throw new InvalidArgumentException("Duplicate payment method handle [{$handle}].");
            }

            $resolved[$handle] = $instance;
        }

        return array_values($resolved);
    }

    public function get(string $handle): ?PaymentMethod
    {
        foreach ($this->all() as $method) {
            if ($method->handle() === $handle) {
                return $method;
            }
        }

        return null;
    }

    /**
     * @param  class-string<PaymentMethod>|PaymentMethod  $method
     */
    private function resolve(string|PaymentMethod $method): PaymentMethod
    {
        return is_string($method) ? $this->container->make($method) : $method;
    }
}

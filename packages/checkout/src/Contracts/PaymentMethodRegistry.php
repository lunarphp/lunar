<?php

namespace Lunar\Checkout\Contracts;

interface PaymentMethodRegistry
{
    /**
     * @param  class-string<PaymentMethod>|PaymentMethod  $method
     */
    public function add(string|PaymentMethod $method): static;

    /**
     * @return array<int, PaymentMethod>
     */
    public function all(): array;

    public function get(string $handle): ?PaymentMethod;
}

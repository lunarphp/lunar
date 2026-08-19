<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Core\Models\Cart;

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

    /**
     * The registered methods this basket can actually use.
     *
     * @return array<int, PaymentMethod>
     */
    public function availableFor(Cart $cart): array;

    public function get(string $handle): ?PaymentMethod;
}

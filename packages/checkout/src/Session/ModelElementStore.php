<?php

namespace Lunar\Checkout\Session;

use Lunar\Checkout\Contracts\ElementDataStore;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * The spec 0010 §C element bag, persisted on the checkout session row.
 *
 * This is what the uuid-addressed flow uses: captured data is then visible to
 * anything that reads the session, survives a supersede, and has a row to
 * serialise writes on.
 */
class ModelElementStore implements ElementDataStore
{
    public function __construct(
        private readonly CheckoutSession $session,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->session->getElementData($key) ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $this->session->putElementData($key, (array) $value);
    }

    public function forget(string $key): void
    {
        $this->session->forgetElementData($key);
    }

    public function all(): array
    {
        return $this->session->element_data?->getArrayCopy() ?? [];
    }
}

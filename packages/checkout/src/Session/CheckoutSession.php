<?php

namespace Lunar\Checkout\Session;

use Illuminate\Contracts\Session\Session as LaravelSession;
use Lunar\Checkout\Contracts\CheckoutSession as CheckoutSessionContract;

/**
 * Laravel-session-backed checkout session (prototype). Namespaces every captured
 * value under a single bag key so element data does not collide with the host
 * app's own session keys. Replaced by the spec 0004 CheckoutSession model
 * (persisted, uuid-addressable, with pinned currency/total) when it lands.
 */
class CheckoutSession implements CheckoutSessionContract
{
    private const BAG = 'lunar.checkout.elements';

    public function __construct(
        private readonly LaravelSession $session,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->bag()[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $bag = $this->bag();
        $bag[$key] = $value;
        $this->session->put(self::BAG, $bag);
    }

    public function forget(string $key): void
    {
        $bag = $this->bag();
        unset($bag[$key]);
        $this->session->put(self::BAG, $bag);
    }

    public function all(): array
    {
        return $this->bag();
    }

    /** @return array<string, mixed> */
    private function bag(): array
    {
        return $this->session->get(self::BAG, []);
    }
}

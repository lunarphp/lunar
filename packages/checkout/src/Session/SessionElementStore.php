<?php

namespace Lunar\Checkout\Session;

use Illuminate\Contracts\Session\Session as LaravelSession;
use Lunar\Checkout\Contracts\ElementDataStore;

/**
 * Element data in the visitor's PHP session. This is the store for the
 * session-less embedded flow, which per spec 0010 §C has no bag row to write
 * to. The uuid-addressed checkout flow uses ModelElementStore instead.
 */
class SessionElementStore implements ElementDataStore
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

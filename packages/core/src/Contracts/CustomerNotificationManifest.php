<?php

namespace Lunar\Core\Contracts;

/**
 * The catalogue of notifications an admin can send to a customer on demand,
 * mapping a key to a label (for the variant dropdown) and a notification class
 * (instantiated and delivered by the NotifyCustomer action).
 *
 * Distinct from `lunar.orders.notifications`, which maps lifecycle *states* to
 * *automatic* sends; this is the *manual* catalogue. Seeded with code-level
 * defaults (empty until the default lifecycle notifications ship); a consumer
 * registers or removes entries from a service provider via register()/forget().
 * Mirrors the {@see ReasonManifest} override seam.
 */
interface CustomerNotificationManifest
{
    /**
     * Register a manually-sendable notification. The label defaults to the key
     * and is resolved through `__()` on read, so a translation key or a plain
     * label both work.
     *
     * @param  class-string  $notification
     */
    public function register(string $key, string $notification, ?string $label = null): static;

    /**
     * The full key => label set, used to populate the variant dropdown.
     *
     * @return array<string, string>
     */
    public function all(): array;

    /**
     * The notification class registered under a key, or null when none is.
     *
     * @return class-string|null
     */
    public function get(string $key): ?string;

    /**
     * The label for a registered key, falling back to the raw key when it is
     * not in the set, or null when no key is given.
     */
    public function label(?string $key): ?string;

    /**
     * Remove one or more notifications by key.
     */
    public function forget(string ...$keys): static;

    /**
     * Whether the catalogue has no notifications. The admin action is hidden
     * while this is true.
     */
    public function isEmpty(): bool;
}

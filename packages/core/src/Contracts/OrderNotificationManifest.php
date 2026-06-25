<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Enums\NotificationScope;

/**
 * The single catalogue of notifications sent to a customer about an order.
 *
 * Each entry is one notification that can fire two ways, both properties of the
 * same entry rather than separate registries: *automatically* when the order or
 * a fulfilment enters one of its trigger states (`on`), and/or *manually* from
 * the admin (`manual`) — so a notification that failed to send automatically
 * (an undelivered order confirmation) can be resent by hand. Its `scope` decides
 * what it is constructed with and where it sends from ({@see NotificationScope}).
 *
 * Container-bound and seeded with code-level defaults, mirroring the
 * {@see ReasonManifest} override seam; a consumer registers or removes entries
 * from a service provider. This replaces the former `lunar.orders.notifications`
 * config map — class references belong in the container, not config.
 */
interface OrderNotificationManifest
{
    /**
     * Register (or replace) a notification.
     *
     * @param  class-string  $notification
     * @param  string|null  $label  Dropdown label; defaults to the key, resolved through `__()` on read.
     * @param  array<int, string>  $on  Status / state `$name`s that auto-fire it; empty means manual-only.
     * @param  bool  $manual  Whether it appears in the admin send list (and so can be resent).
     */
    public function register(
        string $key,
        string $notification,
        ?string $label = null,
        array $on = [],
        bool $manual = true,
        NotificationScope $scope = NotificationScope::Order,
    ): static;

    /**
     * Remove one or more notifications by key.
     */
    public function forget(string ...$keys): static;

    /**
     * The notification class registered under a key, or null when none is.
     *
     * @return class-string|null
     */
    public function get(string $key): ?string;

    /**
     * The label for a key, falling back to the raw key when not registered, or
     * null when no key is given.
     */
    public function label(?string $key): ?string;

    /**
     * The key => label set of manually-sendable notifications for a scope —
     * populates the admin send dropdowns.
     *
     * @return array<string, string>
     */
    public function sendable(NotificationScope $scope = NotificationScope::Order): array;

    /**
     * The notification classes auto-fired when a machine of the given scope
     * enters $status — used by the listeners.
     *
     * @return array<int, class-string>
     */
    public function triggeredBy(string $status, NotificationScope $scope = NotificationScope::Order): array;
}

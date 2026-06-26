<?php

namespace Lunar\Core\Manifests;

use Lunar\Core\Contracts\OrderNotificationManifest as OrderNotificationManifestContract;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Notifications\OrderUpdate;

class OrderNotificationManifest implements OrderNotificationManifestContract
{
    /**
     * The registered notifications, keyed by their notification key.
     *
     * @var array<string, array{notification: class-string, label: string, on: array<int, string>, manual: bool, scope: NotificationScope}>
     */
    protected array $notifications = [];

    public function __construct()
    {
        foreach ($this->defaults() as $key => $definition) {
            $this->register($key, ...$definition);
        }
    }

    /**
     * The code-level default catalogue. Ships a single general-purpose,
     * manual-only "order update" notification so the admin can compose a send
     * out of the box; the branded, auto-triggered lifecycle notifications are a
     * separate piece of work. A consumer forgets or re-registers a key to swap
     * it.
     *
     * @return array<string, array{0: class-string, 1?: string, 2?: array<int, string>, 3?: bool, 4?: NotificationScope}>
     */
    protected function defaults(): array
    {
        return [
            'order-update' => [OrderUpdate::class, 'lunar::notifications.order_update.label'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(
        string $key,
        string $notification,
        ?string $label = null,
        array $on = [],
        bool $manual = true,
        NotificationScope $scope = NotificationScope::Order,
    ): static {
        $this->notifications[$key] = [
            'notification' => $notification,
            'label' => $label ?? $key,
            'on' => $on,
            'manual' => $manual,
            'scope' => $scope,
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function forget(string ...$keys): static
    {
        foreach ($keys as $key) {
            unset($this->notifications[$key]);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?string
    {
        return $this->notifications[$key]['notification'] ?? null;
    }

    public function label(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        return isset($this->notifications[$key])
            ? (string) __($this->notifications[$key]['label'])
            : $key;
    }

    /**
     * {@inheritDoc}
     */
    public function sendable(NotificationScope $scope = NotificationScope::Order): array
    {
        $sendable = [];

        foreach ($this->notifications as $key => $definition) {
            if ($definition['manual'] && $definition['scope'] === $scope) {
                $sendable[$key] = (string) __($definition['label']);
            }
        }

        return $sendable;
    }

    /**
     * {@inheritDoc}
     */
    public function triggeredBy(string $status, NotificationScope $scope = NotificationScope::Order): array
    {
        $triggered = [];

        foreach ($this->notifications as $definition) {
            if ($definition['scope'] === $scope && in_array($status, $definition['on'], true)) {
                $triggered[] = $definition['notification'];
            }
        }

        return $triggered;
    }
}

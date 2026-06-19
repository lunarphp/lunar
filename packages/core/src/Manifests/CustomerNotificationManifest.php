<?php

namespace Lunar\Core\Manifests;

use Lunar\Core\Contracts\CustomerNotificationManifest as CustomerNotificationManifestContract;
use Lunar\Core\Notifications\OrderUpdate;

class CustomerNotificationManifest implements CustomerNotificationManifestContract
{
    /**
     * The registered notification classes, keyed by their variant key.
     *
     * @var array<string, class-string>
     */
    protected array $notifications = [];

    /**
     * The variant labels, keyed by their variant key. Core seeds translation
     * keys, which are resolved through `__()` on read; a consumer may set plain
     * labels.
     *
     * @var array<string, string>
     */
    protected array $labels = [];

    public function __construct()
    {
        foreach ($this->defaults() as $key => [$notification, $label]) {
            $this->register($key, $notification, $label);
        }
    }

    /**
     * The code-level default catalogue. Ships a single general-purpose "order
     * update" notification so the NotifyCustomer action works out of the box;
     * the branded lifecycle notifications are a separate piece of work. A
     * consumer forgets or re-registers `order-update` to swap it.
     *
     * @return array<string, array{0: class-string, 1: string}>
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
    public function register(string $key, string $notification, ?string $label = null): static
    {
        $this->notifications[$key] = $notification;
        $this->labels[$key] = $label ?? $key;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return array_map(fn (string $label): string => (string) __($label), $this->labels);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?string
    {
        return $this->notifications[$key] ?? null;
    }

    public function label(?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        return isset($this->labels[$key]) ? (string) __($this->labels[$key]) : $key;
    }

    /**
     * {@inheritDoc}
     */
    public function forget(string ...$keys): static
    {
        foreach ($keys as $key) {
            unset($this->notifications[$key], $this->labels[$key]);
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->notifications === [];
    }
}

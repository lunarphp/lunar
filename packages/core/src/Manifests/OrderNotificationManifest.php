<?php

namespace Lunar\Core\Manifests;

use Lunar\Core\Contracts\OrderNotificationManifest as OrderNotificationManifestContract;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Notifications\OrderCancellation;
use Lunar\Core\Notifications\OrderConfirmation;
use Lunar\Core\Notifications\OrderProvisioned;
use Lunar\Core\Notifications\OrderReadyForCollection;
use Lunar\Core\Notifications\OrderShipped;
use Lunar\Core\Notifications\OrderUpdate;
use Lunar\Core\Notifications\PartialFulfilmentUpdate;
use Lunar\Core\Notifications\PaymentReceived;
use Lunar\Core\Notifications\RefundIssued;
use Lunar\Core\Notifications\ReturnReceived;

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
     * The code-level default catalogue: the branded lifecycle emails, each
     * keyed to the status or event name that fires it, plus two manual-only
     * variants. Fulfilment-scoped entries are per-fulfilment states only (never
     * the order rollups, which would double-send) and are not manually
     * sendable, because the "Notify customer" composers are order-scoped. A
     * consumer forgets or re-registers a key to switch one off or swap it.
     *
     * @return array<string, array{0: class-string, 1?: string, 2?: array<int, string>, 3?: bool, 4?: NotificationScope}>
     */
    protected function defaults(): array
    {
        return [
            'order-confirmation' => [OrderConfirmation::class, 'lunar::notifications.order_confirmation.label', ['placed'], true, NotificationScope::Order],
            'payment-received' => [PaymentReceived::class, 'lunar::notifications.payment_received.label', ['paid'], true, NotificationScope::Order],
            'order-shipped' => [OrderShipped::class, 'lunar::notifications.order_shipped.label', ['shipped'], false, NotificationScope::Fulfilment],
            'order-ready-for-collection' => [OrderReadyForCollection::class, 'lunar::notifications.order_ready_for_collection.label', ['ready-for-collection'], false, NotificationScope::Fulfilment],
            'order-provisioned' => [OrderProvisioned::class, 'lunar::notifications.order_provisioned.label', ['provisioned'], false, NotificationScope::Fulfilment],
            'return-received' => [ReturnReceived::class, 'lunar::notifications.return_received.label', ['returned'], false, NotificationScope::Fulfilment],
            'order-cancelled' => [OrderCancellation::class, 'lunar::notifications.order_cancelled.label', ['cancelled'], true, NotificationScope::Order],
            'refund-issued' => [RefundIssued::class, 'lunar::notifications.refund_issued.label', ['refund-issued'], true, NotificationScope::Order],
            'partial-fulfilment-update' => [PartialFulfilmentUpdate::class, 'lunar::notifications.partial_fulfilment_update.label', [], true, NotificationScope::Order],
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

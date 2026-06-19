# 0035 — Interactive "Notify customer" order action

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-06-17
- TODO item: Order lifecycle follow-up — restore the ad-hoc customer notification (compose + send)

## Problem

v1 let an admin, from the order screen, **compose and send a customer email on demand**: pick which notification variant to send (a checklist of configured mailers), optionally add a **custom message** that appeared in the email, choose recipients (billing / shipping contact + an ad-hoc address), and every send was **recorded on the order timeline** (an `email-notification` activity, with the rendered email stored).

[[0022-order-fulfilments]] replaced the hand-driven order status with derived rollups and moved notifications to automatic, event-driven listeners. That is the right model for *reliable* lifecycle emails, but it dropped the *interactive* send entirely. Today the only manual control is the cancellation `notify` on/off toggle ([[0025-order-cancellation]]). There is no way to:

- send a customer an ad-hoc update ("sorry for the delay", "we've confirmed your address"),
- choose which notification to send, or attach a one-off message, or
- see what was emailed and when.

The audit surface is also half-dismantled: the `Admin\Support\ActivityLog\Orders\EmailNotification` renderer and its `email-notification.blade.php` partial are **still registered** in the order timeline, but **nothing produces the event anymore** — the v1 producer (the `UpdatesOrderStatus` trait) was removed with the headline status. The renderer is orphaned.

## Proposal

An order-level **"Notify customer"** action that composes and sends a chosen notification, and re-homes the orphaned audit renderer by giving it a producer again. It is deliberately **order-level**, not fulfilment-level: the recipient is the order's contact and ad-hoc messages are about the order as a whole. Where a message needs parcel context — tracking — the action can reference a fulfilment (see Open questions).

### `NotifyCustomer` action

Core `Actions\Orders\NotifyCustomer` (`Contracts\Actions\Orders\NotifiesCustomer`), surfaced as a model verb:

```php
$order->notifyCustomer(string $notification, ?string $message = null, array $recipients = []): Order
```

- `$notification` — a key from the **`OrderNotifications` registry** (below).
- `$message` — optional free text, included in the email.
- `$recipients` — explicit recipient emails; defaults to the order's billing + shipping `contact_email`.
- Effect: instantiate the notification, deliver it to each recipient, **log an `email-notification` activity** on the order, and dispatch `OrderCustomerNotified($order, $notification, $recipients)`.

Putting the send + activity log in the core action (not the Filament layer, as v1 did) means an API caller records the same audit trail as the admin.

### `OrderNotifications` registry (single catalogue)

There is **one** notification catalogue, not a manual one and a separate automatic one — "auto-triggered" and "manually sendable" are properties of an entry, not separate registries. (An earlier draft split them; [[0037-notification-manifests]] then proposed splitting the automatic side further. Both are superseded: a customer who never got their automatic order confirmation must be able to have it resent by hand, so the sets overlap and belong together.)

The `OrderNotifications` manifest (code defaults + override seam, mirroring `CancelReasons` / `HoldReasons` from [[0033-reduce-config-surface]], and replacing the former `lunar.orders.notifications` config map) holds entries that each declare how they can fire:

```php
OrderNotifications::register(
    key: 'order-confirmation',
    notification: OrderConfirmation::class,
    label: 'Order confirmation',
    on: ['placed'],     // status / state names that auto-fire it; omit for manual-only
    manual: true,       // appears in the admin send list (so it can be resent)
    scope: NotificationScope::Order, // Order payload, or Fulfilment for per-parcel sends
);
```

- The automatic listeners resolve `OrderNotifications::triggeredBy($name, $scope)` (replacing the config lookup).
- The manual action's variant dropdown is `OrderNotifications::sendable(NotificationScope::Order)`.
- **Scope** decides the payload and where it sends from: order-scoped notifications receive the `Order` and send from the order header; fulfilment-scoped ones receive a `Fulfilment` and are resent from the parcel (which carries the tracking context). The per-fulfilment resolution still flows through the method-aware `FulfilmentStateConfig::notificationsFor()` seam, now reading the registry instead of config.

Core ships one default — a manual-only, order-scoped `OrderUpdate` (general-purpose, renders the admin's message) — so the action works out of the box; the branded, auto-triggered lifecycle notifications are [[0036-default-customer-notifications]]. The admin action is hidden only when no order-scoped notification is manually sendable.

### Custom message

Manually-sendable notifications accept an optional message. A light contract — `Contracts\Notifications\AcceptsCustomerMessage`, or simply a documented `(Order $order, ?string $message = null)` constructor — lets `NotifyCustomer` pass `$message` through. Notifications that don't support a message ignore the second argument.

### Recipients

Default to the unique, non-empty set of `$order->billingAddress?->contact_email` and `$order->shippingAddress?->contact_email`; the modal pre-checks these and allows adding one ad-hoc address (the v1 affordance).

### Audit trail — re-home `EmailNotification`

`NotifyCustomer` logs an `email-notification` activity per recipient, restoring the producer the renderer expects:

```php
activity()->causedBy(auth()->user())->performedOn($order)
    ->event('email-notification')
    ->withProperties(['notification' => $label, 'email' => $recipient, 'message' => $message])
    ->log('email-notification');
```

The existing `email-notification.blade.php` is updated to read `notification` (the chosen variant label) + `email` + an optional `message` snippet, replacing its current `mailer` / `email` read. **Rendered-HTML storage is dropped** — v1 wrote each email to disk (`Storage::put`) and the v2 partial already notes "preview is not ported"; persisting customer emails as HTML blobs is a privacy / retention liability for little gain. The timeline records *that* an email was sent, to whom, which variant, and the message — not a frozen copy. (Re-introducing a preview is an Open question.)

### Admin

A **"Notify customer"** header action (`Filament\Actions\Orders\NotifyCustomerAction`, bridge package, beside `CancelOrderAction`) opens a modal: **Notification** (select, from the registry), **Message** (textarea, optional), **Recipients** (checkboxes of the order contacts + an "additional email" field). Visible only when the registry is non-empty. Delegates to `$record->notifyCustomer(...)`.

## Alternatives considered

- **Keep manual and automatic notifications in separate registries.** Initially proposed (a manual catalogue distinct from the automatic `lunar.orders.notifications` map), then rejected: a notification that fires automatically (order confirmation) must also be resendable by hand when it fails to arrive, so the sets overlap. "Auto-triggered" and "manually sendable" became per-entry properties of one `OrderNotifications` registry instead, which also collapses three would-be registries into one.
- **Put the action on the fulfilment / parcel.** Rejected: the recipient is an order contact and ad-hoc messages are order-scoped. Parcel context (tracking) is better pulled into an order-level compose than scattered as a second composer per parcel.
- **Store the rendered email (v1 behaviour).** Rejected for now — privacy / retention cost; the metadata log covers the audit need. Revisit as a preview feature.
- **A dedicated `order_notifications` table.** Rejected: the activity log already models and renders per-order timeline events; a parallel table duplicates it.
- **Do nothing (lean on automatic notifications + the cancel toggle).** Rejected: it leaves a real v1 capability regression and an orphaned renderer.

## Migration impact

- **No database migration** — audit uses the existing activity log.
- **Additive public surface:** `Contracts\Actions\Orders\NotifiesCustomer`, `Actions\Orders\NotifyCustomer`, `Order::notifyCustomer()` (+ the model contract), `Events\Orders\OrderCustomerNotified`, the `OrderNotifications` manifest + facade, the `NotificationScope` enum, an optional `AcceptsCustomerMessage` notification contract, and `Filament\Actions\Orders\NotifyCustomerAction`.
- **Breaking:** the `OrderNotifications` registry replaces the `lunar.orders.notifications` config map (the automatic listeners read it now). In v2 that key only ever shipped commented-out examples, so the change is effectively additive for current installs; a consumer who set it moves the entries to `OrderNotifications::register(...)` in a service provider.
- **`EmailNotification` renderer** gains a producer; `email-notification.blade.php` is updated (reads `notification` / `email` / `message` instead of `template` / `mailer`).
- **Translations (16 locales):** new `lunar-filament::actions.orders.notify_customer.*` (label, modal heading, field labels, success), English first then mirrored; plus any default registry labels, following the `CancelReasons` label convention.
- **Filament / admin impact:** a new header action on the order page; verify at `https://lunar-v2.test`.
- **Depends on** the "ship default customer notifications" TODO for the registry's default entries — until those land the action is present but hidden (empty registry).

## Open questions

- **Attach fulfilment tracking.** Should the compose modal optionally pull a parcel's tracking into the message / notification (for an ad-hoc "shipped" update)? Deferred — land the order-level composer first, add a tracking reference if demand appears.
- **Rendered preview.** Whether to re-introduce a stored / rendered preview of the sent email (v1 had one, unported). Deferred; the metadata log is the v2 baseline.
- **Resend.** Whether a logged send offers a one-click "resend". Out of scope.

## References

- [[0022-order-fulfilments]] — moved notifications to automatic listeners; the interactive send this restores was dropped there.
- [[0025-order-cancellation]] — the one surviving manual notification control (`notify` toggle); this is its general-purpose sibling.
- [[0033-reduce-config-surface]] — the `CancelReasons` / `HoldReasons` manifest pattern the `OrderNotifications` registry mirrors, and the config-surface principle that retires `lunar.orders.notifications`.
- [[0037-notification-manifests]] — superseded; its config→manifest migration is absorbed into this single `OrderNotifications` registry.
- [[0034-fulfilment-notifications]] — wires + gates the *automatic* fulfilment notifications; this covers the *interactive* send. Complementary.
- TODO: "Ship default professional customer notifications for the order lifecycle" — supplies the notification classes this action sends.

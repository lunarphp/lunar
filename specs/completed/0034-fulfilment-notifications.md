# 0034 — Wire and gate fulfilment notifications

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-17
- TODO item: Order fulfilments follow-up — connect the per-parcel notification path and gate automatic sends

## Problem

[[0022-order-fulfilments]] sends order-level notifications off the derived rollups, and [[0025-order-cancellation]] gates the cancellation email behind a `notify` toggle. Two gaps remain on the fulfilment side:

- **The per-parcel notification path is stubbed but never connected.** `FulfilmentObserver::updated()` dispatches `FulfilmentStatusUpdated($fulfilment, $previous, $new)` on every parcel state change, and `FulfilmentStateConfig::notificationsFor(FulfilmentState $state)` resolves the notifications configured for a parcel state — but **nothing listens to the event and nothing calls `notificationsFor()`**. The comment in `config/orders.php` claims per-parcel notifications are sent "by `DefaultFulfilmentStateConfig::notificationsFor()`", yet that method has no caller. So the single most useful fulfilment notification — *"your parcel has shipped, here's the tracking"* — never fires, even when a consumer configures `lunar.orders.notifications.shipped`. Only the order-level rollups (`paid`, `fulfilled`, …) actually send.

- **Automatic fulfilment notifications can't be suppressed per operation.** `SendOrderFulfilmentStatusNotifications` fires unconditionally whenever the rollup changes. Cancellation lets the admin decide per-action (`OrderCancelled::$notify`), but marking a parcel shipped / returned / fulfilled has no equivalent — there is no way to make a quiet correction without emailing the customer, and no on-screen signal that the action will email them at all.

Net: the per-parcel customer email is wired to the edge of dispatch and dropped, and the rollup emails that do fire are all-or-nothing.

## Proposal

Two coordinated changes — connect the per-parcel path, and give every fulfilment-driven notification a per-operation `notify` gate consistent with cancellation.

### Per-parcel notification listener

A new `Listeners\SendFulfilmentStatusNotifications` consumes `FulfilmentStatusUpdated` and resolves through the method-aware seam rather than reading config directly:

```php
class SendFulfilmentStatusNotifications
{
    public function __construct(
        protected FulfilmentStateConfig $config,
    ) {}

    public function handle(FulfilmentStatusUpdated $event): void
    {
        if (! $event->notify) {
            return;
        }

        foreach ($this->config->notificationsFor($event->newStatus) as $class) {
            $event->fulfilment->order->notify(new $class($event->fulfilment));
        }
    }
}
```

- Registered alongside the existing listeners in `LunarServiceProvider` (`Event::listen(FulfilmentStatusUpdated::class, SendFulfilmentStatusNotifications::class)`).
- The notification is instantiated with the **`Fulfilment`** (not the order), so a shipped-parcel notification can read its tracking and lines; it is still delivered to the order's contact via the order's existing `Notifiable` routing (`$event->fulfilment->order->notify(...)`).
- Resolution goes through `FulfilmentStateConfig::notificationsFor()` — finally giving the contract method a caller — so per-parcel notifications stay method-aware (a custom fulfilment method maps its own states to notifications per [[0031-fulfilment-methods]]). The order-rollup listeners keep their flat `config()` lookup; the asymmetry is deliberate — the per-parcel catalogue belongs to the method, the rollup catalogue is global.

This makes the `config/orders.php` comment true: a configured `lunar.orders.notifications.shipped` (or any per-parcel state) finally sends.

### Per-operation `notify` gate

The `notify` intent rides on the events, mirroring `OrderCancelled`:

- `FulfilmentStatusUpdated` and `OrderFulfilmentStatusUpdated` each gain `public bool $notify = true` (additive; `OrderCancelled` already carries it).
- The state-advancing fulfilment verbs gain a trailing `bool $notify = true`: `Fulfilment::ship(array $tracking = [], bool $notify = true)`, `fulfil(bool $notify = true)`, `markReturned(bool $notify = true)`, `transition(string $state, bool $notify = true)`. Their actions set a **transient (non-persisted) suppression flag** on the `Fulfilment` instance before the state write.
- `FulfilmentObserver` reads that flag when it fires `FulfilmentStatusUpdated`, and passes the same intent into `RecomputesOrderStatus::execute(Order $order, bool $notify = true)`, which stamps it onto `OrderFulfilmentStatusUpdated`. Default `true` everywhere preserves every existing caller.

The result: unticking "notify" on a single ship / return suppresses **both** the per-parcel email *and* the order-rollup email the same operation triggers (e.g. shipping the final parcel, which also flips the order to `fulfilled`). The admin's intent — "don't email the customer about this change" — is honoured regardless of which configured notification would have fired.

Payment-status notifications are out of scope: they derive from the ledger, not a hand-driven action, so there is no operation to attach a toggle to.

### Admin

- A **"Notify customer"** `Toggle` (default on) is added to the fulfilment status-change modals — `shipAction()` first (the canonical tracking-bearing send), then `returnAction()` / `fulfilAction()` / `transitionAction()`. Its value threads into the verb (`->ship($tracking, notify: $data['notify'] ?? true)`).
- **Conditional visibility:** the toggle is shown only when a notification is actually configured for the destination state (`FulfilmentStateConfig::notificationsFor($targetState)` is non-empty). When nothing is configured the toggle is hidden and the action keeps its current confirmation-only shape — so the control never appears as a tick that does nothing, and its *presence* becomes the cue that the action will email the customer. This mirrors v1's progressive disclosure, which hid the mailer/message fields when no mailers were configured for a status.
- For consistency, `CancelOrderAction`'s `notify` toggle (shipped always-on in 0025) adopts the same rule: shown only when `lunar.orders.notifications.cancelled` is configured. Minor behavioural change, noted below.

### Config

No new keys. The existing `lunar.orders.notifications` block documents the now-live per-parcel example and the differing payload:

```php
'notifications' => [
    // 'shipped'   => [App\Notifications\OrderShipped::class],   // per-parcel state — receives the Fulfilment
    // 'fulfilled' => [App\Notifications\OrderFulfilled::class],  // order fulfilment_status — receives the Order
],
```

The shipped defaults themselves remain the province of the "ship default customer notifications" TODO — this spec wires and gates the path; that work fills it with branded classes.

## Alternatives considered

- **Gate only the per-parcel notification, ignore the rollup.** Simpler (no `RecomputesOrderStatus` change), but shipping the final parcel with "notify" off would still fire the order-level `fulfilled` email — violating the admin's intent. Rejected.
- **A transient suppression flag on the Order, no event changes.** Listeners read a flag off the order. Rejected: notification listeners may be queued (`SerializesModels`) and a transient property does not survive serialization; carrying `notify` on the event is queue-safe and matches the established `OrderCancelled` shape.
- **Always show the toggle, disabled with a "no notification configured" hint when empty.** More discoverable but adds a permanently-disabled control to every modal; conditional visibility keeps modals clean and makes presence meaningful. Rejected in favour of hiding.
- **Do nothing / document that consumers must wire their own listener.** Rejected: the contract method, the event, and the config comment all already promise the per-parcel path works; leaving it dead is a latent bug, not a design choice.

## Migration impact

- **No database migration.**
- **Additive public surface:** new `Listeners\SendFulfilmentStatusNotifications`; optional `bool $notify = true` added to `FulfilmentStatusUpdated`, `OrderFulfilmentStatusUpdated`, the four `Fulfilment` verbs, and `RecomputesOrderStatus::execute()`. All defaulted, so existing call sites and consumer subclasses keep compiling.
- **Behavioural changes (no Rector target — documented in the upgrade notes):**
  - A configured per-parcel notification (e.g. `lunar.orders.notifications.shipped`) now **sends**, where before it silently did not. The keys ship commented-out, so only deliberate configuration is affected — but a consumer who set one expecting nothing will start emailing.
  - `CancelOrderAction`'s notify toggle is now hidden when no `cancelled` notification is configured.
- **`FulfilmentStateConfig::notificationsFor()`** gains its first caller; no signature change.
- **Translations (16 locales):** one new admin key for the toggle label, `lunarpanel::order.fulfilments.actions.notify` (English first, mirrored across the other 15). No removals.
- **Filament / admin impact:** the fulfilment status-change modals gain the conditional toggle; verify end-to-end at `https://lunar-v2.test`.

## Open questions

- **Hold / release, parcel-cancel and location changes** don't correspond to a customer-facing milestone, so they get no toggle. Confirmed in scope: only `ship` / `fulfil` / `markReturned` / `transition` carry one (and the toggle still only appears when that destination state has a configured notification).
- **Double-send when both per-parcel and rollup notifications are configured** (e.g. `shipped` *and* `fulfilled`) is left to the consumer's config choice; the gate suppresses both together but does not de-duplicate. A future "notification policy" could dedupe — out of scope here.

## References

- [[0022-order-fulfilments]] — the rollups, events, observers and `notificationsFor()` seam this completes.
- [[0025-order-cancellation]] — the `notify`-toggle pattern this generalises to fulfilment actions.
- [[0031-fulfilment-methods]] — fulfilment methods own their per-parcel states; per-parcel notifications resolve through the method-aware `FulfilmentStateConfig`.
- [[0035-notify-customer-action]] — the *interactive* customer send; complementary to this *automatic* path.
- TODO: "Ship default professional customer notifications for the order lifecycle" — supplies the notification classes this path delivers.

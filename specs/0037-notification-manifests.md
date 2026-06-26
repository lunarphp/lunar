# 0037 — Move automatic notifications onto manifests (split by payload)

- Status: superseded by [[0035-notify-customer-action]]
- Author: Glenn Jacobs
- Created: 2026-06-19
- TODO item: Reduce config surface follow-up — the order/fulfilment notification registry is the last class-list still living in config

> **Superseded.** This spec proposed two payload-split manifests for the *automatic* notifications, kept separate from 0035's *manual* catalogue. That separation was then dropped entirely: an automatic notification (e.g. order confirmation) must be manually resendable, so the manual and automatic sets overlap and belong in **one** registry. The outcome — a single `OrderNotifications` manifest whose entries carry `on` (auto-trigger), `manual`, and `scope` (Order / Fulfilment) — is implemented under [[0035-notify-customer-action]], which also retires `lunar.orders.notifications`. The payload distinction this spec was built around survives as the `NotificationScope` enum on each entry, not as a separate manifest. Retained for the design trail.

## Problem

v2 registers every other registry in the container as a manifest — `CancelReasons`, `HoldReasons`, `Carriers`, `FulfilmentMethods`, and (from [[0035-notify-customer-action]]) `CustomerNotifications`. The convention is explicit: *config is for values, the container is for substitutions; don't put class references in `config('lunar.*')`*. [[0033-reduce-config-surface]] moved the reason sets off config for exactly this reason.

The **automatic** lifecycle notifications are the one hold-out — and they have a second smell on top. They register as a single flat map of notification **class-strings** in `config('lunar.orders.notifications')`, keyed by state `$name`, but that one map spans four event sources whose **payloads differ**:

| Key example | Source | Payload the notification receives | Sent by |
|---|---|---|---|
| `paid`, `refunded` | order `payment_status` | **Order** | `SendOrderPaymentStatusNotifications` |
| `fulfilled` | order `fulfilment_status` rollup | **Order** | `SendOrderFulfilmentStatusNotifications` |
| `cancelled` | order cancellation | **Order** | `SendOrderCancelledNotifications` |
| `shipped` | per-fulfilment state | **Fulfilment** | `SendFulfilmentStatusNotifications` (via `FulfilmentStateConfig::notificationsFor()`) |

So a consumer registering under `paid` gets an `Order`, under `shipped` a `Fulfilment`, and nothing in the API says which. That payload-depends-on-the-key ambiguity is the same thing the "cross-machine key collision" worry in the earlier draft was circling. A generic single registry (the previous `LifecycleNotifications` name) papers over it; naming it `FulfilmentNotifications` would be **wrong** — three of the four sources are order-level and hand over an `Order`.

## Proposal

Split the registry by payload into two manifests, each precise about what its notifications receive, and retire `lunar.orders.notifications`. Both stay distinct from `CustomerNotifications` (the manual, admin-sendable catalogue from [[0035-notify-customer-action]]); the whole notification surface then lives in the container, none in config.

### `OrderStatusNotifications` — Order payload

Core `Manifests\OrderStatusNotificationManifest` (`Contracts\OrderStatusNotificationManifest`), singleton-bound, `OrderStatusNotifications` facade. Keyed by an order status `$name` (payment_status, fulfilment_status rollup, plus `cancelled`); every registered notification is constructed with the **`Order`**.

```php
interface OrderStatusNotificationManifest
{
    /** Append notifications fired when the order enters $status. @param class-string ...$notifications */
    public function register(string $status, string ...$notifications): static;

    /** Replace the whole map — the override seam. @param array<string, array<class-string>> $map */
    public function set(array $map): static;

    public function forget(string ...$statuses): static;

    /** Notifications for a status, empty when none. @return array<class-string> */
    public function get(string $status): array;

    /** @return array<string, array<class-string>> */
    public function all(): array;
}
```

The three order listeners take it as a constructor-injected collaborator and read `->get($name)` / `->get('cancelled')`.

```php
use Lunar\Core\Facades\OrderStatusNotifications;

OrderStatusNotifications::register('paid', \App\Notifications\PaymentReceived::class);
OrderStatusNotifications::register('cancelled', \App\Notifications\OrderCancelled::class);
```

### Fulfilment-state notifications — Fulfilment payload

These keep their existing home: the method-aware `FulfilmentStateConfig::notificationsFor(FulfilmentState $state)` seam, which already fronts them and is already a container contract. The only change is **where the default implementation reads from** — a `FulfilmentNotifications` manifest (`Manifests\FulfilmentNotificationManifest` / `Contracts\…` / facade) keyed by fulfilment state `$name`, instead of config. `DefaultFulfilmentStateConfig::notificationsFor()` resolves `FulfilmentNotifications::get($state::$name)`; method-awareness is unchanged (the state→method mapping still gates which states a parcel can reach). Every registered notification is constructed with the **`Fulfilment`**.

```php
use Lunar\Core\Facades\FulfilmentNotifications;

FulfilmentNotifications::register('shipped', \App\Notifications\OrderShipped::class); // receives the Fulfilment
```

A consumer who needs fully custom resolution still binds their own `FulfilmentStateConfig` (the existing swap seam); the manifest is just the ergonomic default store behind it. This is deliberately *not* folded into the `FulfilmentMethod` contract — keeping it on the state-config seam avoids a contract change while still being method-correct.

### Config

Drop the `notifications` block from `config/orders.php`; replace the comment with a pointer to the two facades and the README. No other key in that file changes.

## Alternatives considered

- **One registry, renamed `OrderNotifications`.** Rejected: keeps the payload-depends-on-key ambiguity; the name still has to cover a Fulfilment-payload case.
- **Name it `FulfilmentNotifications` (the literal "scope it to fulfilments" suggestion).** Rejected: three of the four sources are order-level and receive an `Order`; a fulfilment-only name mislabels the payment and cancellation sends.
- **Fold per-fulfilment notifications into the `FulfilmentMethod` contract.** Considered — most cohesive, since a method already owns its state graph — but it's a contract change on top of the split; deferred. The state-config seam already gives method-correct resolution.
- **Leave it in config.** Rejected: it's the lone class-registry in config, contradicts 0033 / the container convention, and forces [[0036-default-customer-notifications]] to ship defaults as config stubs.

## Migration impact

- **No database migration.**
- **Public-surface change:** two new manifests + facades + contracts (`OrderStatusNotifications`, `FulfilmentNotifications`) — additive. **Removal** of the read of `config('lunar.orders.notifications')` is the breaking part. `FulfilmentStateConfig`'s contract is unchanged (only its default body).
- **Upgrade path:** a temporary boot-time bridge in core imports any present `config('lunar.orders.notifications')` entries into the right manifest — routing each key to `FulfilmentNotifications` when it matches a registered fulfilment state `$name`, otherwise to `OrderStatusNotifications` — and emits a deprecation notice. It stays one minor cycle, then is removed, so existing apps upgrade with no code change. A Rector rule in `upgrade` annotates the stale `notifications` block in a published `config/orders.php` and points at the two facades; Rector can't rewrite a config array into provider calls, so the bridge does the real migration and the rule is advisory (call this limitation out in the PR, per the upgrade conventions).
- **Translations:** none.
- **Filament / admin impact:** `CancelOrderAction`'s toggle gate switches from `config('lunar.orders.notifications.cancelled')` to `OrderStatusNotifications::get('cancelled')`; `OrderFulfilments::notifyToggle()` already routes through `FulfilmentStateConfig::notificationsFor()`, so it's unaffected. Verify the cancel + ship/fulfil/return toggles still appear only when a notification is registered, at `https://lunar-v2.test`.

## Open questions

- **Names.** `OrderStatusNotifications` + `FulfilmentNotifications` read well and state their payload; confirm before implementing (renaming a facade later needs a Rector rule).
- **Is `FulfilmentNotifications` worth a manifest, or is binding `FulfilmentStateConfig` enough?** A manifest keeps registration ergonomically symmetric with the order side; the counter-argument is one more facade. Leaning manifest.
- **Bridge routing.** Confirm the "match a registered fulfilment state name => FulfilmentNotifications, else OrderStatusNotifications" heuristic is unambiguous given the (small) risk of a name shared across machines; document the precedence.
- **Bridge lifetime / loudness.** One-minor-cycle window; warn via log or import silently.

## References

- [[0033-reduce-config-surface]] — moved the reason sets off config; this finishes the job for notifications.
- [[0035-notify-customer-action]] — `CustomerNotifications`, the manual sibling these align with; the pattern to mirror.
- [[0034-fulfilment-notifications]] — wired the per-fulfilment sends through `FulfilmentStateConfig::notificationsFor()`; this re-homes their registry off config.
- [[0036-default-customer-notifications]] — registers its branded defaults in these manifests' `defaults()` (Order-payload sends in `OrderStatusNotifications`, the shipped-with-tracking send in `FulfilmentNotifications`) rather than config stubs.

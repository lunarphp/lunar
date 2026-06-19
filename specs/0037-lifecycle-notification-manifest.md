# 0037 — Move automatic lifecycle notifications onto a manifest

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-19
- TODO item: Reduce config surface follow-up — the order/fulfilment notification registry is the last class-list still living in config

## Problem

v2 registers every other registry in the container as a manifest — `CancelReasons`, `HoldReasons`, `Carriers`, `FulfilmentMethods`, and (from [[0035-notify-customer-action]]) `CustomerNotifications`. The convention is explicit: *config is for values, the container is for substitutions; don't put class references in `config('lunar.*')`*. [[0033-reduce-config-surface]] moved the reason sets off config for exactly this reason.

The **automatic** lifecycle notifications are the one hold-out. They register as a map of notification **class-strings** in config:

```php
// config/orders.php
'notifications' => [
    'paid'      => [App\Notifications\PaymentReceived::class],   // payment_status — receives the Order
    'shipped'   => [App\Notifications\OrderShipped::class],      // per-fulfilment state — receives the Fulfilment
    'cancelled' => [App\Notifications\OrderCancelled::class],    // order cancellation — receives the Order
],
```

read directly by:

- `Listeners\SendOrderPaymentStatusNotifications`, `SendOrderFulfilmentStatusNotifications`, `SendOrderCancelledNotifications` — `config('lunar.orders.notifications.<state>')`.
- `States\Fulfilment\DefaultFulfilmentStateConfig::notificationsFor()` — same key, the per-fulfilment path. (This one is already fronted by the `FulfilmentStateConfig` contract; only its default body reaches into config.)
- `Filament\Actions\Orders\CancelOrderAction` — gates the "notify customer" toggle on `filled(config('lunar.orders.notifications.cancelled'))`.

So one feature (interactive sends, 0035) follows the manifest convention while its sibling (automatic sends, [[0034-fulfilment-notifications]]) registers classes through config. That is the inconsistency: a registry of classes living in config, which 0033 set out to eliminate.

## Proposal

Introduce a `LifecycleNotifications` manifest — the container seam for *automatic, state-keyed* sends — and retire the `lunar.orders.notifications` config key. Keep it **separate** from `CustomerNotifications`: 0035 deliberately split the automatic set (keyed by lifecycle *state*) from the manual, admin-sendable set (an arbitrary key + label). Both now live in the container; neither lives in config.

### `LifecycleNotificationManifest`

Core `Manifests\LifecycleNotificationManifest` (`Contracts\LifecycleNotificationManifest`), singleton-bound in `LunarServiceProvider::registerServices()`, with a `LifecycleNotifications` facade — mirroring the existing manifests:

```php
interface LifecycleNotificationManifest
{
    /** Append notifications fired when a machine enters $state (its `$name`). @param class-string ...$notifications */
    public function register(string $state, string ...$notifications): static;

    /** Replace the whole map — the override seam. @param array<string, array<class-string>> $map */
    public function set(array $map): static;

    /** Drop all notifications for one or more state keys. */
    public function forget(string ...$states): static;

    /** Notifications registered for a state key, empty when none. @return array<class-string> */
    public function get(string $state): array;

    /** The full state => notifications map. @return array<string, array<class-string>> */
    public function all(): array;
}
```

`defaults()` returns `[]` — parity with today's empty config. [[0036-default-customer-notifications]] populates the branded defaults *here* (in `defaults()`), not in a config stub.

Consumers register from a service provider, exactly like the other seams:

```php
use Lunar\Core\Facades\LifecycleNotifications;

LifecycleNotifications::register('paid', \App\Notifications\PaymentReceived::class);
LifecycleNotifications::register('shipped', \App\Notifications\OrderShipped::class); // per-fulfilment state
```

### Resolution stays where it is

The flat-key-by-state-`$name` lookup is preserved 1:1 — the same key serves payment_status, fulfilment_status, per-fulfilment state, and `cancelled`. Call sites switch from `config()` to the manifest:

- The three order listeners take the manifest as a constructor-injected collaborator (service-layer DI) and read `->get($name)` / `->get('cancelled')`.
- `DefaultFulfilmentStateConfig::notificationsFor()` resolves from the injected manifest. The `FulfilmentStateConfig` contract is unchanged, so the method-aware per-fulfilment seam keeps working — a custom fulfilment method's states still map their own `$name`s to notifications.
- `CancelOrderAction`'s toggle gate becomes `filled(LifecycleNotifications::get('cancelled'))`; `OrderFulfilments::notifyToggle()` already routes through `notificationsFor()`, so it needs no change.

### Config

Drop the `notifications` block (currently commented examples) from `config/orders.php`; replace the comment with a one-line pointer to `LifecycleNotifications` and the README. No other key in that file changes.

## Alternatives considered

- **Fold automatic + manual into one notifications manifest.** Rejected: 0035 argued the sets are genuinely different (an admin sends "delay apology", never "paid"); the automatic set is keyed by state, the manual by arbitrary key + label. Two manifests, one pattern.
- **Leave it in config.** Rejected: it's the lone class-registry in config and contradicts 0033 / the container convention; it also forces 0036 to ship defaults as config stubs rather than `defaults()`.
- **Do it inside 0036.** Considered (and offered), but the seam migration is orthogonal to *which* notifications ship — separating them keeps 0036 about content and this about the seam, and lets the migration land first so 0036's defaults go straight into the manifest.

## Migration impact

- **No database migration.**
- **Public-surface change:** new `Contracts\LifecycleNotificationManifest`, `Manifests\LifecycleNotificationManifest`, `Facades\LifecycleNotifications` (additive). **Removal** of the read of `config('lunar.orders.notifications')` — a documented config key — is the breaking part.
- **Upgrade path:** a temporary boot-time bridge in core imports any present `config('lunar.orders.notifications')` entries into the manifest and emits a deprecation notice; it stays one minor cycle, then is removed. This gives existing apps a zero-touch upgrade. A Rector rule in `upgrade` annotates the stale `notifications` block in a published `config/orders.php` and points at the manifest — Rector can't rewrite a config array into service-provider calls, so the bridge does the actual migration and the rule is advisory (call this limitation out in the PR, per the upgrade conventions).
- **Translations:** none (no user-facing strings).
- **Filament / admin impact:** `CancelOrderAction` toggle gate switches to the facade; the fulfilment modals are unaffected (already on the contract). Verify the cancel + ship/fulfil/return toggles still appear only when a notification is registered, at `https://lunar-v2.test`.

## Open questions

- **Name.** `LifecycleNotifications` vs `OrderNotifications` — the latter matches the old config key but reads oddly for the per-fulfilment `shipped` send. Leaning `LifecycleNotifications`.
- **Cross-machine key collisions.** The flat key by state `$name` is shared across machines (as today). Worth a documented note, or a future split into per-machine namespaces? Out of scope here — preserve current behaviour.
- **Bridge lifetime.** Confirm the one-minor-cycle deprecation window is right, and whether the bridge should warn loudly (log) or silently import.

## References

- [[0033-reduce-config-surface]] — moved the reason sets off config; this finishes the job for notifications.
- [[0035-notify-customer-action]] — `CustomerNotifications`, the manual sibling this aligns with; the pattern to mirror.
- [[0034-fulfilment-notifications]] — wired the automatic per-fulfilment sends through `FulfilmentStateConfig::notificationsFor()`; this re-homes their registry.
- [[0036-default-customer-notifications]] — should register its branded defaults in this manifest's `defaults()` rather than a config stub.

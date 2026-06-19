# 0036 — Default professional customer notifications for the order lifecycle

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-06-19
- TODO item: Ship default professional customer notifications for the order lifecycle

## Problem

The system already *expects* lifecycle notifications but core ships almost none:

- `lunar.orders.notifications` keys off `paid` / `fulfilled` / `cancelled`, and the per-parcel fulfilment config keys off `shipped`, but every entry is an empty/commented example. Out of the box a customer is never emailed and a developer must hand-roll each `Notification` class plus its mail template.
- [[0025-order-cancellation]] dispatches `OrderCancelled` with a "notify" toggle, [[0034-fulfilment-notifications]] wires + gates the per-parcel `shipped` send, and [[0035-notify-customer-action]] adds the interactive "Notify customer" action — all three are plumbing waiting on notification *content*.
- [[0035-notify-customer-action]] ships exactly one stop-gap default in the `CustomerNotifications` catalogue: a general-purpose `OrderUpdate` (built on `MailMessage`, renders the admin's free-text message) so the interactive action is usable. There is still no order-confirmation, payment-received, shipped-with-tracking, cancelled, or refunded email.

The result: the toggles and actions exist, but flipping them sends nothing professional, and there is no shared, branded mail layout to build on.

## Proposal

Ship a set of sensible, branded-but-overridable default `Notification` classes plus mail templates for the key lifecycle events, and wire them as the defaults for both the automatic listeners and the interactive catalogue.

### Notification classes

Core `Notifications\…`, each `(Order $order, ?string $message = null)`, `via() = ['mail']`, rendering through a shared markdown mail layout:

- `OrderConfirmation` — order placed.
- `PaymentReceived` — payment captured / order paid.
- `OrderShipped` — a fulfilment shipped; pulls tracking (number / URL / carrier) into the email. Constructed with the `Fulfilment` so it carries parcel context (mirrors [[0034-fulfilment-notifications]]'s method-aware seam).
- `OrderCancelled` — order cancelled, with the cancel reason label.
- `RefundIssued` — a refund was processed, with the amount.

All implement `Contracts\Notifications\AcceptsCustomerMessage` so they slot into the interactive action and render an optional admin message. Keep `OrderUpdate` (from [[0035-notify-customer-action]]) as the catch-all.

### Shared mail layout

A publishable markdown mail layout (`resources/views/mail/…`) all the defaults extend, themeable via the standard `vendor:publish` of Laravel's notification components plus a Lunar branding header (store name / logo). Ties into the storefront / branding work.

### Wiring the defaults

- Register the lifecycle defaults so the existing listeners and notify toggles send something — the Order-payload sends (`paid` => `PaymentReceived`, `fulfilled` => `OrderFulfilled`, `cancelled` => `OrderCancelled`) in the `OrderStatusNotifications` manifest's `defaults()`, and the Fulfilment-payload `shipped` => `OrderShipped` in the `FulfilmentNotifications` manifest's `defaults()` ([[0037-notification-manifests]]) — not config stubs — assuming that seam split lands first. Respect the existing `notify` flags — a quiet ship / cancel still suppresses the send.
- Register the relevant variants in the `CustomerNotifications` catalogue ([[0035-notify-customer-action]]) so an admin can also send them on demand.
- Order confirmation fires on order placed (new listener or the existing placement path).

### Overridability

Every default stays swappable: re-bind the notification, re-register the catalogue key, or publish the templates. A per-event "off" switch (config or empty registration) disables one without code.

## Alternatives considered

- **Leave it to consumers (status quo).** Rejected: the toggles/actions look functional but do nothing; "works out of the box" is a project principle (sensible defaults over subclassing).
- **One mega-notification keyed by event.** Rejected: a class per event is clearer to override, test, and translate, and matches how the config/registry already key by event.
- **Build only the interactive `OrderUpdate` (done in 0035) and stop.** Rejected: that covers ad-hoc sends but leaves every automatic lifecycle email empty.

## Migration impact

- **No database migration.**
- **Additive public surface:** new `Notifications\…` classes, a shared mail layout, default config entries, default `CustomerNotifications` registrations. Re-binding / re-registering / publishing are the override seams.
- **Behavioural shift:** apps that left `lunar.orders.notifications` at the defaults will *start* sending customer emails once defaults are populated. Call this out prominently; gate behind a clear opt-out and document it in the upgrade notes.
- **Translations (16 locales):** subject / greeting / body lines per notification under `lunar::notifications.*`, English first then mirrored.
- **Filament / admin impact:** the catalogue gains the new variants in the "Notify customer" modal; the automatic notify toggles ([[0025-order-cancellation]], [[0034-fulfilment-notifications]]) become meaningful. Verify at `https://lunar-v2.test`.

## Open questions

- **Branding source.** Where the layout reads store name / logo / colours from — ties into the storefront / branding work; may need a small settings seam.
- **Order confirmation trigger.** Whether confirmation hangs off an existing placement event or a new one.
- **Default on or off.** Whether populated defaults send automatically on upgrade, or ship registered-but-disabled so an existing store doesn't suddenly email customers. Leaning to opt-in for existing installs, on for fresh installs.

## References

- [[0035-notify-customer-action]] — interactive send + the `CustomerNotifications` catalogue + the stop-gap `OrderUpdate` default this set extends.
- [[0034-fulfilment-notifications]] — per-parcel shipped notification wiring + the method-aware seam `OrderShipped` plugs into.
- [[0025-order-cancellation]] — the cancellation notify toggle `OrderCancelled` fills in.
- [[0033-reduce-config-surface]] — the manifest pattern the catalogue mirrors.
- [[0037-notification-manifests]] — moves the automatic notifications onto payload-split manifests; this spec's defaults register there.

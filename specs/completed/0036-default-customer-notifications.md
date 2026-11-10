# 0036 — Default professional customer notifications for the order lifecycle

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-19
- TODO item: Ship default professional customer notifications for the order lifecycle

## Problem

The notification plumbing is complete but the catalogue is nearly empty:

- The single `OrderNotifications` manifest ([[0035-notify-customer-action]]) ships one default: a manual-only `OrderUpdate` so the "Notify customer" action works. Nothing is registered with an `on:` trigger, so none of the five wired listeners (`SendOrderPaymentStatusNotifications`, `SendOrderFulfilmentStatusNotifications`, `SendFulfilmentStatusNotifications`, `SendOrderCancelledNotifications`, `SendOrderRefundedNotifications`) ever sends anything out of the box.
- The admin toggles that gate those sends look functional but are inert: the Filament cancel action hides its "notify" toggle because nothing is registered for `cancelled`, and the panel's fulfilment transitions report `notify: false` because `notificationsFor(Shipped)` is empty.
- A store owner therefore gets no order confirmation, no shipped-with-tracking email, no cancellation or refund email until a developer writes each `Notification` class and template by hand. The same is true of the other fulfilment methods: `Collection` has a `ready-for-collection` state and `Digital` a `provisioned` state, both offered with a notify toggle in the panel, and neither sends. A return recorded through `markReturned()` is likewise silent.
- There is no `OrderPlaced` notification listener at all, so even a consumer-registered order confirmation cannot fire automatically.
- Core has no view namespace, so there is nowhere to put a shared, publishable mail template.

Two latent defects surface as soon as defaults are registered:

- **`refunded` is two triggers at once.** The payment state `Refunded` has `$name = 'refunded'`, and `SendOrderRefundedNotifications` also looks up `'refunded'`. A full refund creates a refund transaction (recompute → `OrderPaymentStatusUpdated`, ungated) *and* dispatches `OrderRefunded` (gated on `notify`), so one registration sends twice.
- **`placed` and `paid` land in the same checkout.** The Stripe and PayPal drivers record the capture and stamp `placed_at` in one flow, so a confirmation on `placed` plus a payment email on `paid` would double-email every card checkout. (`OfflinePayment` is the opposite case: it places the order with no capture, and `paid` only arrives when an admin captures later.)

## Proposal

Ship nine branded-but-overridable default notifications (eight auto-triggered lifecycle emails plus one manual-only variant), the listener and view namespace they need, and register them in the manifest with sensible triggers. Fix the two trigger defects on the way.

### Notification classes

Core `Notifications\…`, following the `OrderUpdate` pattern (`Queueable`, `via() = ['mail']`, markdown `MailMessage`):

| Class | Constructor | Purpose |
| --- | --- | --- |
| `OrderConfirmation` | `(Order $order, ?string $message = null)` | Order placed. Reference, placed date, line summary, totals, addresses, payment status. |
| `PaymentReceived` | `(Order $order, ?string $message = null)` | A capture took the order to `paid` *after* placement (authorize-then-capture, offline payment marked paid in the admin). |
| `OrderShipped` | `(Fulfilment $fulfilment, ?string $message = null)` | A `Shipping` fulfilment shipped. Lines in the parcel plus each tracking entry: carrier name, tracking number, `url`. |
| `OrderReadyForCollection` | `(Fulfilment $fulfilment, ?string $message = null)` | A `Collection` fulfilment reached `ready-for-collection`. Lines to collect and the fulfilment's location. |
| `OrderProvisioned` | `(Fulfilment $fulfilment, ?string $message = null)` | A `Digital` fulfilment reached `provisioned`. Lines provisioned; no download link, since core records none (a storefront that does can re-register the key). |
| `ReturnReceived` | `(Fulfilment $fulfilment, ?string $message = null)` | A fulfilment was marked `returned`. Lines received back; pairs with `RefundIssued` when a refund follows. |
| `OrderCancellation` | `(Order $order, ?string $message = null)` | Order cancelled, with `$order->cancelReasonLabel()`. Named to avoid clashing with `Events\Orders\OrderCancelled` in listener imports. |
| `RefundIssued` | `(Order $order, ?string $message = null)` | A refund was processed. Amount read from the ledger: the latest successful `$order->refunds()` transaction. |
| `PartialFulfilmentUpdate` | `(Order $order, ?string $message = null)` | Manual-only. Lists the lines still outstanding (ordered quantity minus the quantity on fulfilled fulfilments) so "part of your order is delayed" is a pick-and-send instead of free text. |

The order-scoped classes implement `Contracts\Notifications\AcceptsCustomerMessage` and render the optional admin message, so they slot into the interactive action. The fulfilment-scoped classes do not implement it (the contract documents an `Order` constructor). `OrderUpdate` stays as the catch-all.

Recipient routing is the order's default: billing contact, falling back to shipping (`Order::routeNotificationForMail()`). None of the defaults implement `RoutesToOrderContact` or `ResolvesOrderMailRoute`; a consumer subclass can.

### Mail templates

- Core gains `resources/views/mail/…`, loaded as the `lunar` view namespace, published under a `lunar.views` tag.
- Each notification renders through `MailMessage::markdown('lunar::mail.orders.<name>')`. The views extend Laravel's `x-mail::message` layout, so branding (header name from `config('app.name')`, colours) is themed through the standard `vendor:publish --tag=laravel-mail` and needs no new seam. Logo and store colours are deferred to a future branding spec; the layout choice makes that a drop-in swap.
- Shared partials: `lunar::mail.partials.order-summary` (lines, quantities, sub total, discount, shipping, tax, total, formatted through the order's price formatting), `lunar::mail.partials.fulfilment-lines` (the lines on one fulfilment, used by every fulfilment-scoped email and by the outstanding-lines list), `lunar::mail.partials.tracking` (one row per `FulfilmentTracking`), `lunar::mail.partials.address` (one order address) and the `greeting` / `signoff` partials every template opens and closes with.
- Every string comes from `lunar::notifications.<key>.*` (subject, heading, intro, outro), with the greeting, sign-off and table headings shared under `lunar::notifications.partials.*`, so a published view and a translated locale compose.

### Listener changes

- **New `SendOrderPlacedNotifications`** on `OrderPlaced`: resolves `triggeredBy('placed', NotificationScope::Order)` and sends with the order. No `notify` flag: placement is customer-initiated.
- **Status rollups only email placed orders.** `SendOrderPaymentStatusNotifications` and `SendOrderFulfilmentStatusNotifications` return early while `! $order->isPlaced()`. This is what keeps `paid` quiet at card checkout: drivers record the capture, the rollup fires on an unplaced order and is skipped, then `placed_at` is stamped and the confirmation (which shows the payment state) goes out. A later capture on a placed order fires `payment-received` as intended. It also stops draft orders emailing customers.
- **The refund trigger is renamed.** `SendOrderRefundedNotifications` resolves `triggeredBy('refund-issued')` instead of `'refunded'`. `refunded` and `partially-refunded` keep their meaning as ungated payment-status rollups. `cancelled` already sets the precedent for an event key that is not a state name.
- **Fulfilment-scoped sends need no new listener.** `SendFulfilmentStatusNotifications` already resolves every per-parcel state through `FulfilmentStateConfig::notificationsFor()`, so `shipped` (via `ship()`), `ready-for-collection` (via `transition()`), `provisioned` (via `fulfil()`) and `returned` (via `markReturned()`) all fire from the existing path and honour its `notify` flag.

### Registering the defaults

`OrderNotificationManifest::defaults()` becomes:

| Key | Class | `on` | `manual` | Scope |
| --- | --- | --- | --- | --- |
| `order-confirmation` | `OrderConfirmation` | `['placed']` | true | Order |
| `payment-received` | `PaymentReceived` | `['paid']` | true | Order |
| `order-shipped` | `OrderShipped` | `['shipped']` | false | Fulfilment |
| `order-ready-for-collection` | `OrderReadyForCollection` | `['ready-for-collection']` | false | Fulfilment |
| `order-provisioned` | `OrderProvisioned` | `['provisioned']` | false | Fulfilment |
| `return-received` | `ReturnReceived` | `['returned']` | false | Fulfilment |
| `order-cancelled` | `OrderCancellation` | `['cancelled']` | true | Order |
| `refund-issued` | `RefundIssued` | `['refund-issued']` | true | Order |
| `partial-fulfilment-update` | `PartialFulfilmentUpdate` | `[]` | true | Order |
| `order-update` | `OrderUpdate` | `[]` | true | Order |

- The fulfilment-scoped entries are not manually sendable because the `NotifyCustomer` action and both admin composers are order-scoped; there is no fulfilment-scoped resend path today. The manifest already answers `sendable(NotificationScope::Fulfilment)`, so a consumer can build one; a first-party parcel resend is deferred (it is also 0035's "attach fulfilment tracking" open question).
- Registering per-parcel states only (not the `fulfilled` or `returned` order rollups) avoids the per-parcel / rollup double-send noted in [[0034-fulfilment-notifications]].
- `collected` gets no email: the customer is standing at the counter. `in-progress`, `pending` and the fulfilment `cancelled` state are back-office transitions and stay silent; an order-level cancellation is covered by `order-cancelled`.
- Existing `notify` flags still gate every event-driven send: a quiet ship, collection-ready, provision, return, cancel or refund sends nothing.

### Overridability

- Re-register a key with your own class to replace one, or `OrderNotifications::forget('payment-received')` to switch one off. No config key: class substitution belongs in the container, values in config ([[0033-reduce-config-surface]]).
- Publish `lunar.views` to change a template, `laravel-mail` to change the layout, `lunar.translation` to change copy.

### Default on

Defaults are registered and active for every install. Every send except the placement confirmation is already behind a per-action `notify` toggle, and switching one off is a one-line `forget()` in a service provider. An "off for existing installs" variant would need an install-time marker that does not exist and would leave fresh installs and upgraded installs behaving differently. The behavioural shift is called out in `WHATS-NEW.md` and the upgrade notes with the `forget()` recipe.

## Alternatives considered

- **Leave it to consumers (status quo).** Rejected: the toggles and actions look functional but do nothing; "works out of the box" is a project principle.
- **One mega-notification keyed by event.** Rejected: a class per event is clearer to override, test and translate, and matches how the manifest keys entries.
- **A Lunar-owned mail layout with its own branding config.** Rejected for now: Laravel's markdown layout already provides a themeable header and colours, and no branding seam exists in core or the panel to read from. Adding one belongs to a branding spec.
- **Carry the refund amount on `OrderRefunded`.** Rejected: a manual resend only has `(Order, ?message)`, so the notification must be able to find the amount from the ledger anyway. Reading the latest refund transaction serves both paths.
- **Suppress `payment-received` by checking the previous status was `authorized`/`pending`.** Rejected: it does not distinguish "captured at checkout" from "captured later"; the placed gate does.
- **Keep `refunded` as the refund-event key and de-duplicate in the listeners.** Rejected: two listeners sharing a key with different gating semantics is the bug; a distinct key is simpler.

## Migration impact

- **No database migration.**
- **Additive public surface:** nine `Notifications\…` classes, the `lunar` view namespace and `lunar.views` publish tag, `Listeners\SendOrderPlacedNotifications`, nine new manifest default keys.
- **Behavioural changes:**
  - Installs on the default manifest start sending customer emails for placement, later payment, shipping, collection-ready, digital provisioning, returns, cancellation and refunds. Documented with the opt-out recipe.
  - `SendOrderRefundedNotifications` now resolves `refund-issued`. A consumer who registered a notification with `on: ['refunded']` for the refund event keeps the payment-status rollup send (ungated) and loses the event send; the upgrade notes tell them to switch the key. Also stops the double-send for anyone who had hit it.
  - Payment and fulfilment status rollup emails no longer fire for unplaced orders.
- **Translations (16 locales):** `lunar::notifications.<key>.*` for each of the nine notifications plus the shared partial headings, English first, then translated into each of the other 15 locales in that locale's existing vocabulary. No mirrored placeholders.
- **Admin impact:** the "Notify customer" dropdown in both the Inertia panel and the Filament bridge gains five entries (`order-confirmation`, `payment-received`, `order-cancelled`, `refund-issued`, `partial-fulfilment-update`). The Filament cancel action's notify toggle becomes visible; the panel's fulfilment transitions report `notify: true` for `shipped`, `ready-for-collection`, `provisioned` and `returned`. Verify both at `https://lunar-v2.test`.
- **Drivers:** the placed gate relies on drivers recording the capture before stamping `placed_at`. Stripe (`StoreCharges` runs before the `placed_at` update in `UpdateOrderFromIntent`) and PayPal (`createMany` before `update`) both do; `OfflinePayment` records no capture at checkout, so `paid` arrives later and `payment-received` fires as intended. Slice 1 adds a test per driver so the ordering cannot regress silently. Third-party drivers that stamp first will double-send at checkout; the contract docs for `PaymentType` state the ordering.

## Open questions

- **Fulfilment-scoped resend.** Whether a first-party "resend shipping email" on the parcel belongs here or in a follow-up. Deferred to a follow-up unless review says otherwise.
- **Queueing.** Defaults follow `OrderUpdate` and send synchronously (`Queueable` without `ShouldQueue`). Confirm that stays the default, with consumers subclassing to queue.

## References

- [[0035-notify-customer-action]] — the `OrderNotifications` manifest, `NotifyCustomer` action and the `OrderUpdate` default this set extends.
- [[0034-fulfilment-notifications]] — the per-parcel `shipped` path and `notify` gate `OrderShipped` fills.
- [[0025-order-cancellation]] — the cancellation `notify` toggle and `cancelReasonLabel()` `OrderCancellation` uses.
- [[0028-line-item-refunds]] — the refund ledger `RefundIssued` reads.
- [[0033-reduce-config-surface]] — the manifest pattern and the no-class-substitution-in-config rule.
- [[0037-notification-manifests]] — superseded by 0035.

## Implementation plan

- [x] Slice 1 — Triggers: `SendOrderPlacedNotifications`, placed gate on the two rollup listeners, `refund-issued` key, driver capture-before-place check, listener tests.
- [x] Slice 2 — Views: `lunar` view namespace, `lunar.views` publish tag, order-summary and tracking partials, `OrderConfirmation` + test.
- [x] Slice 3 — `PaymentReceived`, `OrderCancellation`, `RefundIssued`, `PartialFulfilmentUpdate` + rendering tests.
- [x] Slice 4 — Fulfilment-scoped: `fulfilment-lines` partial, `OrderShipped`, `OrderReadyForCollection`, `OrderProvisioned`, `ReturnReceived` + rendering tests.
- [x] Slice 5 — Register defaults, translations for 16 locales, `WHATS-NEW.md` and upgrade notes, panel and Filament verification.

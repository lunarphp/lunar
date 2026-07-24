# 0062 — Panel Orders section

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-07-22
- TODO item: Panel Orders section — list, order view, capture/refund/cancel/fulfil actions (spec 0062)

> Implementation notes (landed): PDF generation lives only in the Filament
> package (`Lunar\Filament\...\DownloadOrderPdfAction`) and the panel depends only
> on `core`, so the "Download PDF" action is **deferred** until PDF generation is
> moved into core — it is not reachable from the panel today. Customer **notify**
> was brought forward into this pass (wired through `OrderNotifications::sendable()`
> and the `order-update` notification). Fulfilment **split/merge** turned out to
> already exist in core (`SplitsFulfilment`/`MergesFulfilments` + `$fulfilment`
> verbs), so those are a smaller follow-up than first assumed. Bulk list actions
> (capture/cancel/export) moved out of slice 1 to sit with their single-order
> siblings; only the row "view" action shipped on the list. Address editing is a
> follow-up (reuses `AddressFormFields`).

## Problem

Orders and Discounts are the last two sections missing from the Inertia panel. Orders
is the prototype's Sales resource (`lunar-v2-ui`: `OrdersList.vue`, `OrderDetail.vue`,
`OrderCreate.vue`, `OrderReturnCreate.vue`, `OrderReturnDetail.vue`), and the panel has
no orders surface at all — the Sales section ships Customers only (spec 0053-era work).

The prototype models a much richer order than core exposes today. It uses a two-axis
status model (a financial `paymentStatus` plus a fulfilment `dispatchStatus`), and layers
on fulfilment split/merge, a full returns lifecycle, goodwill credits and credit notes,
per-dispatch invoice numbering, and a manual order builder with an at-checkout payment
dialog. Core backs a subset of this now:

- **Two derived state machines** (Spatie `ModelState`, recomputed by resolvers) already
  map almost exactly onto the prototype's two axes: `payment_status`
  (`Pending`/`Authorized`/`PartiallyPaid`/`Paid`/`PartiallyRefunded`/`Refunded`/`Voided`)
  and `fulfilment_status`
  (`Unfulfilled`/`PartiallyFulfilled`/`Fulfilled`/`PartiallyReturned`/`Returned`), labels
  in `core` `states.php` (`payment.*`, `fulfilment-status.*`).
- A **headline lifecycle** — `Order::lifecycleStatus()` collapses `cancelled_at` /
  `closed_at` into `cancelled`/`closed`/`open` (`states.order.*`); it is derived, not a
  machine.
- A **transaction ledger** (`type` = `intent`/`capture`/`refund`, `success`, `driver`,
  `amount` minor units, `card_type`/`last_four`, `parent_transaction_id`) driving payment
  status via `ResolvePaymentStatus`.
- **Fulfilments** (`Order::fulfilments()`, `createFulfilment()`, `$fulfilment->ship()`)
  with their own state machine (`Pending`/`InProgress`/`ReadyForCollection`/`Shipped`/
  `Collected`/`Provisioned`/`Cancelled`/`Returned`) and `FulfilmentLine` allocations.
- **Order lines** (polymorphic `purchasable`, `type` physical/digital/shipping,
  quantities, per-line totals, `tax_breakdown`), **order addresses** (one table, `type`
  shipping/billing), `discount_breakdown`/`shipping_breakdown` jsonb, `tags`, a single
  `notes` text field, `meta`, and `LogsActivity` timeline.
- **Order actions** (each a model verb one-line-delegating to a contract):
  `capture`, `refund`, `cancel`, `close`, `reopen`, `notifyCustomer`, plus
  `RecomputeOrderStatus`; cancel reasons from `CancelReasonManifest`; PDF via the
  Filament `DownloadOrderPdfAction`. All money is integer minor units — format with
  `$order->format(...)` / `$order->decimal(...)`.

The **Filament `OrderResource`** (`admin` + `filament` packages) is the feature-parity
baseline: a filtered list (lifecycle/payment/fulfilment status, placed-at range, tags;
search reference/name/postcode/email), a `ManageOrder` view (shipping, fulfilments, line
items, totals, transactions, timeline + customer/addresses/tags/meta aside), and header
actions capture/refund/cancel/close/reopen/notify/download-PDF/add-note gated on each
action's `canRun()`.

Several prototype features have **no core backing** and can't ship in the first pass:

- **Returns** — the prototype has a first-class return with a `requested → approved →
  received → resolved` state machine, its own pages and cards. Core has only the
  `PartiallyReturned`/`Returned` fulfilment rollup states — no return model, no lifecycle.
- **Goodwill credits / credit notes** — entirely prototype-invented; nothing in core.
- **Manual order creation** — the `/orders/new` builder and its "mark paid / send invoice
  / charge card" dialog. Core creates orders only from carts (`Cart::createOrder()`); the
  Filament panel has no manual builder.
- **Per-dispatch invoices with numbering** (`INV-{year}-{00001}`) — core ships one order
  PDF, not per-fulfilment numbered invoices.
- **Fulfilment split / merge** — core exposes `createFulfilment` and `ship`, not the
  prototype's split/merge mutations.

Prototype/core disagreements to settle for the buildable pass:

- **Status vocabulary** — present the core states, not the prototype labels: core
  `Fulfilled` covers the prototype's "Dispatched"; core has no order-level "Delivered"
  (delivery is per-fulfilment `Shipped`/`Collected`); core `Voided` reads where the
  prototype says "Failed"; core adds `PartiallyPaid`. Cancelled is the lifecycle axis, not
  a fulfilment state.
- **Per-line refund/return counters** — the prototype's line hints ("1 returned · 2
  refunded") are not backed: refunds are ledger amounts against the order, returns are
  fulfilment-state rollups. Line rows show quantity and price; refund/return progress is
  summarised at order level, not per line.
- **Channel filter** — the prototype's Web store/POS/Phone/Amazon is backed by
  `channel_id`/`Channel`; the filter lists the store's channels.
- **Invoices/credit-notes sidebar** — replaced in the buildable pass by a single
  "Download PDF" (pro-forma before placement, invoice after), pending the print-template
  and credit-note work.

## Proposal

Ship Orders in phases. **Phase 1** is everything buildable on core today — the list and
the order view, styled to the prototype, following the Customers architecture (core action
delegation, `ResolvesTableExtensions`, hand-shaped array props with a `urls` map, the
`PageHeader`/`PageZone` scaffold, `PageAction`/`TableAction` classes). The core-gap
features land in later phases, each paired with the core work it needs (below), so nothing
in Phase 1 is blocked on core.

### Panel server side (Phase 1)

**`SalesSection`** gains an Orders navigation item (cart icon, before Customers) gated on
`sales:manage-orders` — the handle the Filament `OrderResource` already uses; no new
permission. Registers `orders.index` in `tableExtensions()`. Orders are managed, not
drafted (status changes are ledger/fulfilment driven and visible to every staff member),
so there is no `draftables()` entry and no `useEditDraft`.

**Routes** — prefix `orders`, names `panel.orders.*`, middleware `can:sales:manage-orders`:

- `index` (`GET /`), `show` (`GET /{order}`) — the primary view; no `edit`/`update`
  (there is no full order form).
- Action endpoints, each delegating to a core order verb via its `canRun()` guard:
  `capture` (`POST /{order}/capture`), `refund` (`POST /{order}/refund`),
  `cancel` (`POST /{order}/cancel`), `close` / `reopen` (`POST /{order}/close|reopen`),
  `notify` (`POST /{order}/notify`), `note` (`PUT /{order}/note`),
  `pdf` (`GET /{order}/pdf`).
- Address edit: `addresses.update` (`PUT /{order}/addresses/{address}`) and `tags.update`
  (`PUT /{order}/tags`), under `Route::scopeBindings()`.
- Fulfilments: `fulfilments.store` (`POST /{order}/fulfilments`, allocates unfulfilled
  lines) and `fulfilments.ship` (`POST /{order}/fulfilments/{fulfilment}/ship`, carrier +
  tracking + optional ETA) — split/merge deferred.

**Controllers** (`Http/Controllers/Orders/`):

- `OrderIndexController` (uses `ResolvesTableExtensions`) — Eloquent query with search
  (`q` over reference/customer name/email/postcode), status filters
  (lifecycle/payment/fulfilment), channel filter, placed-at range, tags; sort;
  `->paginate(15)->withQueryString()->through(fn (Order) => [...plain row...])` with
  reference, placed date, customer name/email, item count, payment/fulfilment status keys
  + labels, tags, formatted total, cancelled flag, `show` URL. Plus a cached KPI strip
  (orders 30d, revenue 30d net of refunds, awaiting payment, awaiting fulfilment) as
  filter shortcuts, and `tableProps()`.
- `OrderShowController` — hand-shaped props: order header (reference, both status keys +
  labels, lifecycle, channel, placed-at, total), line items, fulfilments (with lines,
  state, tracking), totals (subtotal/discount/shipping/tax/total/refunded/net from the
  ledger), transactions, `discount_breakdown`, shipping option, customer summary, shipping
  + billing addresses, tags, notes, meta, activity timeline, and a `urls` map for every
  action. Heavy props (activity, transactions) via `Inertia::defer(...)`. Also passes
  per-action `canRun` flags and `availableToRefund` / capturable intents so the Vue only
  shows actions core will accept.
- Action controllers as needed (`OrderCaptureController`, `OrderRefundController`,
  `OrderCancelController`, `OrderFulfilmentController`, …) — thin: validate, call the verb,
  `back()->with('success', __(...))`.

**Requests** (`Http/Requests/Orders/`): `CaptureOrderRequest` (transaction id + amount
within the intent), `RefundOrderRequest` (charge id + amount within `availableToRefund` +
notes), `CancelOrderRequest` (reason in `CancelReasons::all()` + note + notify),
`NotifyOrderRequest`, `OrderNoteRequest`, `OrderAddressRequest`, `ShipFulfilmentRequest`.

**`OrdersTableExtension`** (`Sections/Sales/Tables/`) — `ViewOrderAction` row action
(link to show). Bulk actions limited to what core accepts per record: `CaptureOrdersAction`
(capture where `CaptureOrder::canRun`), `CancelOrdersAction` (cancel where
`CancelOrder::canRun`), `ExportOrdersAction` (CSV). Mark-dispatched-in-bulk is deferred
with fulfilment split/merge. Actions skip records that fail their guard and report the
count.

### Frontend (Phase 1)

`pages/orders/{Index,Show}.vue`, standard scaffold (`PanelLayout` + `PageHeader` +
`PageZone` before/after, enforced by `PageScaffoldTest`):

- **Index** — reuses `DataTable`, `Pagination`, `FilterDropdown`, `KpiCard`,
  `BulkActionsToolbar`, `StatusBadge` (the Customers list pattern). Columns: Order
  (mono reference + cancelled badge), Date, Customer, Items, Payment (badge), Fulfilment
  (badge), Tags, Total (right). Toolbar: search, Payment / Fulfilment / Channel / Tag /
  Date-range / Sort dropdowns, result count. No status tabs — status lives in the
  dropdowns and KPI shortcuts, matching the prototype.
- **Show** — two-column (main + sticky sidebar), matching `OrderDetail.vue`. Main:
  Fulfilments (one card per fulfilment with its allocated lines, state badge, tracking;
  "Create fulfilment" when unfulfilled lines remain; "Mark shipped" via the ship dialog),
  Totals, Discount breakdown (when present), Transactions (type/method/reference/date/
  status/amount), Shipping option, Activity (with add-comment). Sidebar `SideCard`s:
  Status (payment + fulfilment + lifecycle, with Capture/Refund/Close/Reopen/Cancel/Notify
  page actions), Customer, Shipping address (editable), Billing address (editable), Tags,
  Notes, Metadata. A single "Download PDF" header action.

New order components (mirroring the prototype's, trimmed to Phase 1): `FulfilmentCard.vue`,
`OrderLineItemsTable.vue`, `OrderTransactionsTable.vue`, `OrderTotals.vue`,
`DiscountBreakdown.vue`, `AddressCard.vue` + `AddressDialog.vue`, and dialogs
`CaptureDialog.vue`, `RefundDialog.vue`, `CancelOrderDialog.vue`, `FulfilShipDialog.vue`.
Header/status actions are `PageAction` classes on `SalesSection`, not bespoke buttons.

Status badges read core state labels through vue-i18n; tones follow the prototype
(paid/fulfilled = sage, authorized/pending/partial = warn, refunded/voided = danger,
cancelled = archived).

### Translations

New `orders.php` lang group (list/show/actions/dialog strings), English first, mirrored
across all 16 locales; `nav.php` gains `orders`. Status labels are read from core's
existing `states.php`, not duplicated.

### Testing

- **Pest (`tests/panel/Feature/Orders/`)**: index (rows, each filter, search, KPI shape,
  permission gating), show props (fulfilments/lines/totals/transactions/addresses/`urls`,
  deferred props, `canRun` flags), each action endpoint (capture within intent, refund
  within `availableToRefund`, cancel with reason, close/reopen, notify, note, address
  update, fulfilment create + ship) including guard rejection paths, bulk actions
  (skips guarded-out records).
- **Vitest**: `FulfilmentCard`, `OrderLineItemsTable`, `OrderTotals`, the four dialogs,
  status-badge tone mapping.
- `PageScaffoldTest` covers the new pages. PHPStan + Pint as required.

### Deferred phases (core-gap features)

Each ships after Phase 1, gated on the core work noted. Sketched here, specced in full
before implementation (several already have stub specs — see References).

- **Fulfilment split / merge** — core `SplitsFulfilment` / `MergesFulfilment` actions +
  `$fulfilment` verbs, then the prototype's split/merge dialogs and the bulk
  "mark dispatched". Smallest gap; likely the first follow-up.
- **Per-dispatch invoices / print templates** ([[0027-order-print-templates]]) — invoice
  numbering + per-fulfilment documents in core, then the Invoices sidebar card and the
  invoice dropdown. Until then, the single order PDF stands in.
- **Line-item refunds** ([[0028-line-item-refunds]]) — per-line refundable/returnable
  quantity tracking in core, then the prototype's line-scoped refund/return dialog and the
  per-line progress hints.
- **Bulk order operations** ([[0026-bulk-order-operations]]) — the broader bulk-action set
  beyond capture/cancel/export.
- **Returns** — a first-class `Return` model + `requested → approved → received →
  resolved` state machine, actions, and events in core, then `pages/orders/returns/*`,
  `ReturnCard`, `ReturnLinePicker`. Largest gap.
- **Goodwill credits / credit notes** — a credit-note model tied to the transaction ledger
  in core, then the goodwill dialog and the Credit-notes sidebar card. Depends on the
  returns/print work.
- **Manual order creation** — a draft-order builder path (create a draft `Order`, add
  product/custom/fee lines, addresses, shipping, discount) plus a "complete" flow
  (mark-paid / send-invoice / charge-card) mapping to offline/gateway payment types, then
  `OrderCreate.vue` + `OrderPaymentDialog.vue` and a `ProductPicker` reused from Products
  (spec 0057). Substantial core work.

## Alternatives considered

- **A full order Edit form (like Products/Customers) instead of a Show/manage view**:
  rejected — an order is not freely editable; the operations that matter are the guarded
  verbs (capture/refund/cancel/…) and address/tag/note edits. A read-first view with
  scoped action endpoints matches core's model and the Filament `ManageOrder` precedent.
- **Presenting one collapsed status column** (the prototype's occasional single badge):
  rejected — core genuinely has three orthogonal axes (lifecycle, payment, fulfilment);
  showing payment + fulfilment badges plus a cancelled marker is honest and matches the
  Filament table and the prototype's own two-badge rows.
- **Building returns / goodwill / manual creation in the first pass**: rejected — each
  needs new core models/actions and its own spec; phasing ships the list and order view
  (the daily-driver surface) immediately without blocking on core.
- **Edit drafts for orders** (the Customers/Collections autosave pattern): rejected —
  orders have no free-text form to draft; status is ledger/fulfilment driven and shared
  across staff in real time, so immediate, guarded endpoints are correct.
- **Renaming core states to the prototype vocabulary** (Dispatched/Delivered/Failed):
  rejected — the state machines are core public surface shared with Filament and the
  storefront; the panel presents the core labels.

## Migration impact

- **No core changes in Phase 1** — it reuses existing order actions, states, and the
  transaction ledger. Later phases add core models/actions (returns, credit notes,
  split/merge, per-line refund tracking, draft-order builder) under their own specs and
  the alpha migration fold-in rule.
- **Permission**: reuses the existing `sales:manage-orders` handle; no new permission.
- **Translations**: new `orders.php` group + `nav.php` addition, all 16 locales. Status
  labels reuse core's `states.php`.
- **Filament / admin**: untouched — the `OrderResource` and its actions coexist; the panel
  is the successor surface.
- **No new npm dependencies.**

## Open questions

- Does the "Awaiting fulfilment" KPI count `Unfulfilled` + `PartiallyFulfilled` on placed,
  paid orders only, or any open order? (Lean: placed + paid, matching the prototype's
  "paid but not yet shipped".)
- Should the ship dialog's carrier list be a fixed set (prototype's Royal Mail/DPD/UPS/
  Evri tracking-URL builders) or driven by the `Carriers` registry / shipping config?
  Resolve against the shipping package before building `FulfilShipDialog`.
- Bulk CSV export column set and whether it reuses any existing exporter.

## References

- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` —
  `src/pages/{OrdersList,OrderDetail,OrderCreate,OrderReturnCreate,OrderReturnDetail}.vue`,
  `src/data/{orders,orderStatus,fulfilments,returns,returnStatus}.js`,
  `src/components/{FulfilmentCard,OrderLineItemsTable,OrderPaymentDialog,DiscountBreakdown,ReturnCard,ReturnLinePicker,GoodwillCreditDialog,AddressCard}.vue`
- Feature-parity baseline: `admin` `OrderResource` + `filament`
  `Tables/Order/OrderTable`, `Actions/Orders/*`, `Resources/OrderResource/Pages/ManageOrder`.
- [[0049-inertia-panel]] — panel architecture and extension model.
- [[0055-panel-collections-section]] / [[0057-panel-products-section]] — the section
  architecture and shared surfaces (product picker) this reuses.
- [[0026-bulk-order-operations]], [[0027-order-print-templates]],
  [[0028-line-item-refunds]] — related order specs feeding the deferred phases.

## Implementation plan

- [x] Slice 1 — Panel scaffold + list: `SalesSection` Orders nav item + routes,
      `OrderIndexController` (rows, filters, search, KPI strip), `OrdersTableExtension`
      (view row action), `pages/orders/Index.vue`, `orders.php` + `nav.php` lang keys
      (16 locales), tests. (Bulk actions deferred to sit with their single-order siblings.)
- [x] Slice 2 — Order view (read): `OrderShowController` (props + `urls` + deferred
      activity), `pages/orders/Show.vue` with the fulfilment/line/transaction/totals blocks
      inlined and the customer/address/tags/notes/meta side cards, tests. (Dedicated
      discount-breakdown card deferred; discount shows in totals.)
- [x] Slice 3 — Order actions: capture/refund/cancel/notify dialogs + note/tag inline
      editing + close/reopen `PageAction` classes, all delegating to core verbs behind
      their `canRun` guards, with guard-rejection (403) and domain-exception (flash)
      handling, tests. (PDF deferred — not reachable from the panel; address edit deferred.)
- [x] Slice 4 — Fulfilment create + ship: `fulfilments.store` / `fulfilments.ship`
      endpoints, ship dialog with carriers from the `Carriers` registry, tests.
- [ ] Deferred — order-PDF (move generation into core first), address editing, list bulk
      actions (capture/cancel/export), split/merge dialogs, per-dispatch invoices,
      line-item refunds, returns, goodwill credits, manual order creation.

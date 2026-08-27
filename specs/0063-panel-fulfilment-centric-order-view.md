# 0063 — Panel fulfilment-centric order view

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-08-27
- TODO item: Panel order view rebuilt around fulfilments + full fulfilment operations (spec 0063)

> Implementation notes (landed): three review follow-ups shipped with this
> work. **Address editing** (deferred from 0062) — shipping/billing side cards
> gain an edit dialog reusing `AddressFormFields`; updates log the same
> `order-address-update` activity event the Filament admin writes, rendered on
> the order timeline with the changed fields. **Delivery method** — the
> checkout's shipping option (from `shipping_breakdown`, falling back to the
> shipping line) leads the subline of every tracking-method fulfilment card and
> anchors the Shipping section, so the admin knows which service to dispatch
> with. **Extension zones** — the show page adds `sidebar:before` /
> `sidebar:after` `PageZone`s and passes the order and shipping option into all
> four zones, so store-specific prominence (for example a per-option processing
> banner) is an add-on slot component, not a core opinion.

## Problem

Spec [[0062-panel-orders-section]] shipped the panel's order show screen with two
parallel representations of the order's contents: a Fulfilments section *and* a
standalone "Line items" table listing every non-shipping line. That flat table is a
holdover from a pre-fulfilment mental model and it is wrong on core's own terms:

- **Every fulfillable line already lives in a fulfilment.** `EnsureInitialFulfilment`
  runs on order placement and creates one fulfilment per claiming method
  (`shipping` / `collection` / `digital`) covering all fulfillable lines at full
  quantity. Its docblock states the model outright: *"The merchant never creates
  fulfilments by hand; they split these or merge back."* The panel's contents view
  should present that reality — fulfilment cards are the order's contents, not an
  adjunct to a lines table. This is how the design prototype (`OrderDetail.vue` +
  `FulfilmentCard.vue`) and the Filament admin (`OrderFulfilments` component) both
  present an order.
- **The duplication splits the information.** The flat table carries the money detail
  (unit price, line total) while the fulfilment cards show only `quantity ×
  description` — so neither block tells the whole story, and every line renders twice.
- **The panel exposes two fulfilment operations where core has fifteen.** Only
  `fulfilments.store` (create a parcel covering everything outstanding) and
  `fulfilments.ship` exist. Core's action set — split, merge, move lines, fulfil
  (mark collected / provisioned), transition, cancel back to the pool, return and
  undo-return, hold and release, change location, add/remove tracking — is fully
  built with `canRun()` guards and model verbs, and the Filament admin surfaces all
  of it. The panel surfaces none.
- **The "Create fulfilment" button contradicts the split-down model.** Because the
  initial fulfilment always exists, the button's only reachable case is an order
  whose lines were returned to the pool programmatically — and Filament, the parity
  baseline, ships no create action at all.
- **Non-shipping methods are dead ends.** A `collection` or `digital` parcel has no
  terminal action in the panel: the only verb wired is `ship()`, which is illegal for
  non-tracking methods. Method, hold state, and location are invisible on the cards.

0062 anticipated split/merge as "a smaller follow-up"; this spec is that follow-up,
widened to full Filament parity and a corrected contents layout. It supersedes the
Fulfilments/Line-items portion of 0062's Show screen; the rest of that screen
(totals, transactions, sidebar, header actions) is untouched.

## Proposal

Rebuild the order show screen's contents around fulfilment cards, remove the flat
line-items table and the create-fulfilment surface, and expose the full core
fulfilment operation set through per-fulfilment endpoints and dialogs. Decisions
below were settled in review (2026-08-27): full Filament action parity in one pass;
flat table removed in favour of an "Other items" section; create button and endpoint
removed; split/merge via dialogs (the panel idiom), not Filament's inline card modes;
line rows get thumbnails and an expandable price panel (stock levels deferred — the
panel has no inventory surface yet).

### Screen layout (Show.vue main column)

1. **Fulfilments** — one card per fulfilment, the primary contents block. No section
   action button (create is gone); the section header shows the parcel count.
2. **Other items** — only rendered when the order has non-shipping lines with no
   fulfilment allocation (`lines()->where('type', '!=', 'shipping')->withoutFulfilment()`):
   services, non-fulfillable custom purchasables, or lines returned to the pool by a
   programmatic terminal cancel. Same line-row component as the cards, no actions.
3. Totals, Discount breakdown, Transactions, Shipping, Activity — unchanged from 0062.

The standalone "Line items" section is deleted. Shipping lines continue to show in
the Shipping section and totals, never as card rows.

### Fulfilment card anatomy

Mirrors the Filament card and the prototype's `FulfilmentCard.vue`:

- **Header** — leading icon tinted by the state's `FulfilmentStateCategory`
  (Outstanding = warn, Fulfilled = sage, Returned = danger, Cancelled = archived);
  reference (mono, falling back to "Fulfilment #{id}"); state badge (same category
  tones); method badge (`method_label`); "On hold" badge with reason label and the
  note as a tooltip when held. Muted subline: location name, handed-over timestamp
  with a per-method label (Shipped at / Collected at / Provisioned at, fallback
  Fulfilled at), or the item count when outstanding.
- **Primary action button** — the method's terminal verb where available:
  "Mark shipped" (tracking dialog) for `shipping`, "Mark collected" / "Mark
  fulfilled" (confirm) for non-tracking methods.
- **"Update status" dropdown** — the intermediate transitions (In progress, Ready
  for collection, …), built from a server-computed `transitions` list (below).
- **Ellipsis menu** — the guarded action set: Split…, Merge into…, Change location…,
  Add tracking…, Undo return, Hold…, Release hold, Return, and the destructive
  Cancel fulfilment (danger, confirm with a "shipment details are cleared" warning).
  Each item renders only when its server-side `can` flag is true.
- **Lines** — one row per `FulfilmentLine`: product thumbnail (placeholder icon when
  none), description, option badges, identifier, right-aligned `{qty} @ {unit_price}`.
  Clicking a row expands a detail panel: unit price, allocated quantity, sub total,
  discount total, one row per tax-breakdown entry, line total (highlighted), and the
  order-line notes when present.
- **Tracking block** (bottom, only when trackings exist and the method uses
  tracking): per row — carrier name, tracking number linked to the resolved URL,
  shipping-method label, and a remove button (confirm).

### Panel server side

**Routes** — under the existing `orders` prefix, `Route::scopeBindings()` on
`{order}/fulfilments/{fulfilment}`, names `panel.orders.fulfilments.*`:

- `ship` (`POST .../ship`) — kept, request widened (below).
- `fulfil` (`POST .../fulfil`), `transition` (`POST .../transition`),
  `split` (`POST .../split`), `merge` (`POST .../merge`),
  `return` (`POST .../return`), `undo-return` (`POST .../undo-return`),
  `hold` (`POST .../hold`), `release` (`POST .../release`),
  `cancel` (`POST .../cancel`),
  `location.update` (`PUT .../location`),
  `trackings.store` (`POST .../trackings`),
  `trackings.destroy` (`DELETE .../trackings/{tracking}`, `{tracking}` scoped to the
  fulfilment).
- **Removed:** `fulfilments.store`.

**`OrderFulfilmentController`** grows one thin method per endpoint, following the
`OrderActionController` pattern: `abort_unless` on the relevant guard, delegate to
the model verb (`$fulfilment->fulfil()`, `->split($moves)`, `->moveLinesTo($target,
$moves)`, `->markReturned()`, `->transition($state)`, `->hold($reason, $note)`,
`->release()`, `->changeLocation($id)`, `->addTracking($data)`,
`$tracking->remove()`), catch `FulfilmentException` / `CouldNotPerformTransition`
into a flash error, `back()` with a success flash. Guards reuse the core statics
(`SplitFulfilment::canRun()`, `MergeFulfilments::isMergeable()`,
`HoldFulfilment::canRun()`, `ChangeFulfilmentLocation::canRun()`, …); undo-return
and cancel gate on `canTransitionTo()` of the method's `fulfilledState()` /
`defaultState()` respectively, exactly as the Filament component does. Merge
delegates to `moveLinesTo($target, all line quantities)` so an emptied source is
removed — the same semantics as Filament's confirm-merge.

**Requests** (`Http/Requests/Orders/`):

- `ShipFulfilmentRequest` — reshaped to accept `tracking` as an **array of rows**
  (each `carrier` / `shipping_method` / `tracking_number` / `tracking_url`, all
  nullable) plus `notify`, matching `ship(array $tracking, bool $notify)` and the
  Filament repeater.
- `SplitFulfilmentRequest` — `moves` array of `order_line_id => quantity`; each id
  must belong to the fulfilment, each quantity within the allocated quantity, at
  least one unit moved in total, and fewer than the parcel's total (moving
  everything is a merge, not a split).
- `MergeFulfilmentRequest` — `target_id`, validated against the fulfilment's
  computed merge candidates (same order, location, and method; Outstanding).
- `TransitionFulfilmentRequest` — `state` within the server-computed legal targets
  plus `notify`.
- `HoldFulfilmentRequest` — `reason` in `HoldReasons::all()` keys (nullable) +
  `note`.
- `ChangeFulfilmentLocationRequest` — `location_id` exists.
- `FulfilmentTrackingRequest` — one tracking row, for `trackings.store`.
- `FulfilNotifyRequest` (shared shape) — `notify` boolean for fulfil / return /
  undo-return.

**`OrderShowController` props** — the `fulfilments` entry becomes the full card
payload; `lines` and `canCreateFulfilment` are removed:

- Per fulfilment: `id`, `reference`, `method` + `method_label`, `state` +
  `state_label` + `state_category`, `on_hold` + `hold_reason_label` + `hold_note`,
  `location` (name), `shipped_at` + `handed_over_label`, `notes`;
  `lines` (`id`, `quantity`, `description`, `identifier`, `options`, `thumbnail`,
  `unit_price`, `sub_total`, `discount_total`, `tax_breakdown`, `total`,
  `line_notes`); `trackings` (`id`, `carrier`, `carrier_name`, `shipping_method`,
  `tracking_number`, `url`, `destroy_url`); a `can` map (`ship`, `fulfil`, `split`,
  `merge`, `cancel`, `return`, `undo_return`, `hold`, `release`, `change_location`,
  `add_tracking`); `merge_targets` (`id`, label with reference + contents summary);
  a `transitions` list; and a `urls` map for every endpoint above.
- `transitions` is computed server-side, mirroring Filament's `statusTransitions()`:
  start from `state->transitionableStateInstances()` (already method-filtered by
  `MethodAwareTransition`), exclude the method's `defaultState()` (that revert is
  the destructive cancel action), exclude `Cancelled`-category targets
  (programmatic only), and exclude `Fulfilled`-category targets while on hold. Each
  entry carries `state`, `label`, `via` (`ship` / `fulfil` / `return` /
  `transition` — which dialog or endpoint the client routes it to), and `notify`
  (whether `FulfilmentStateConfig::notificationsFor()` is non-empty for the target,
  which is the cue to render the "Notify customer" toggle, default on).
- `otherLines` — the unallocated non-shipping lines, same line shape (no actions).
- `carriers` gains per-carrier `services` (`Carriers::get($key)->getServices()`) so
  the ship/tracking dialogs can offer the dependent shipping-method select; the
  tracking-URL input shows only when no carrier is picked (the carrier derives the
  URL otherwise).
- `holdReasons` (`HoldReasons::all()`), `locations` (all locations, for the
  change-location dialog; the menu item only renders when more than one exists).

### Frontend

New components in `resources/js/components/orders/` (the `dashboard/` folder is the
precedent for page-scoped component folders), each with a colocated vitest file:

- `FulfilmentCard.vue` — the card described above; emits per-action events.
- `FulfilmentLineRow.vue` — thumbnail row + expandable detail panel; reused by the
  Other items section.
- `ShipFulfilmentDialog.vue` — extracted from Show.vue and widened: repeatable
  tracking rows (add/remove), carrier select → dependent services select,
  tracking-URL input only for the no-carrier case, notify toggle.
- `SplitFulfilmentDialog.vue` — quantity stepper per line (0..allocated), running
  "moving N of M" summary, confirm disabled until valid. Split is offered only when
  the parcel's total quantity is greater than 1.
- `MergeFulfilmentDialog.vue` — radio list of merge targets with their contents
  summary.
- `HoldFulfilmentDialog.vue` — reason select + note textarea.
- `AddTrackingDialog.vue` — single tracking row (same fields as ship's rows).
- `ChangeLocationDialog.vue` — location select defaulted to current.
- `FulfilmentConfirmDialog.vue` — shared confirm for fulfil / return / undo-return /
  release / cancel / generic transition, with an optional notify toggle driven by
  the transition's `notify` flag and a danger variant for cancel.

`Show.vue` drops the Line items section, the create-fulfilment button, and its
inline ship dialog; the Fulfilments section becomes a stack of `FulfilmentCard`s and
an Other items section renders when `otherLines` is non-empty. Status-badge tones
map from `state_category`, so consumer-registered custom states render sensibly with
no frontend change.

### Translations

`orders.php` in the panel package gains the new action, dialog, and label strings
(split/merge/hold/release/return/undo-return/cancel/transition/tracking/location,
handed-over labels, other-items heading, flash messages) — English first, mirrored
across all 16 locales. The `create_fulfilment` / `fulfilment_nothing_outstanding` /
`flash_fulfilment_created` keys are removed from all 16. State, method, and
hold-reason labels come from core's existing lang groups via the translations
endpoint, not duplicated.

### Testing

- **Pest (`tests/panel/Feature/Orders/`)** — `OrderShowTest` updated for the new
  props (card payload, transitions list with the three exclusion rules, hold/held
  shape, merge targets, `otherLines`, removed props). `OrderFulfilmentTest` grows
  per-endpoint coverage: happy path + guard rejection for each (ship with multiple
  tracking rows; fulfil on collection/digital and its rejection on `shipping`;
  transition to a legal intermediate and rejection of the default state / cancelled
  category / fulfilled-while-held; split within and beyond allocation; merge across
  matching parcels and rejection across method or location; return / undo-return;
  hold with reason + release; cancel reverting to the pool and clearing
  `shipped_at`; change location; tracking add and scoped remove). The removed
  `fulfilments.store` route asserts 404/405.
- **Vitest** — `FulfilmentCard` (action gating off `can`, badge tones off category,
  primary verb per method), `FulfilmentLineRow` (expansion, price panel),
  `ShipFulfilmentDialog` (repeatable rows, carrier/services dependency, URL-field
  visibility), `SplitFulfilmentDialog` (clamping, validity), `MergeFulfilmentDialog`,
  `FulfilmentConfirmDialog` (notify toggle presence).
- `PageScaffoldTest` unaffected. PHPStan + Pint as required.

## Alternatives considered

- **Keep the flat line-items table alongside the cards** — rejected: it duplicates
  every line, splits the money detail from the fulfilment context, and neither the
  prototype nor Filament ships one. The expandable line rows carry the price detail
  the table used to hold.
- **Keep "Create fulfilment" as a fallback for unallocated lines** — rejected: the
  split-down model means the case is only reachable after a programmatic terminal
  cancel, Filament offers no create action either, and the Other items section still
  makes such lines visible. If a real recovery need appears, a re-fulfil action can
  be specced against `EnsureInitialFulfilment`/`CreateFulfilment` later.
- **Filament's inline split/merge card modes** — rejected for the panel: every other
  panel operation (capture, refund, cancel, ship) is a dialog, and a dialog isolates
  the quantity form from the card's action chrome. The prototype uses dialogs too.
- **Computing available transitions client-side** — rejected: the exclusion rules
  (default state, cancelled category, held ⋯ fulfilled) belong with the guards in
  PHP; the client renders what the server says is legal, and the endpoints re-check
  regardless.
- **Exposing a generic transition endpoint only (no dedicated verbs)** — rejected:
  ship/fulfil/return carry extra behaviour (tracking, notify routing) and core's
  seams are per-verb actions; the panel mirrors the same routing Filament uses.

## Migration impact

- **No core changes.** Every operation already exists as a core action with a
  `canRun()` guard and a model verb; this is panel routes, props, and Vue only.
- **Panel surface (pre-release, unmerged):** the `fulfilments.store` endpoint and
  `canCreateFulfilment` / `lines` props are removed — 0062's orders branch has not
  merged, so nothing shipped is broken.
- **Translations:** new keys added and three keys removed across all 16 panel
  locales; core lang groups untouched.
- **Filament / admin:** untouched — it remains the parity baseline.
- **No new npm dependencies.**

## Open questions

None — layout, action scope, create-button removal, split/merge UX, line-row depth,
and branch strategy were resolved in review (2026-08-27). Stock levels in the line
detail panel are deferred until the panel grows an inventory surface, and per-line
refund/return actions remain with [[0028-line-item-refunds]].

## References

- [[0062-panel-orders-section]] — the Orders section this reshapes; its Show-screen
  Fulfilments/Line-items layout is superseded by this spec.
- [[0022-order-fulfilments]] — the fulfilment model, state machine, and operations.
- [[0031-fulfilment-methods]] — methods, state categories, `fulfil()`, and the
  Filament card behaviour mirrored here.
- [[0030-fulfillable-order-lines]] — `requires_fulfilment` / `fulfillableLines()`.
- Filament reference: `admin` `OrderFulfilments` component +
  `order-fulfilments.blade.php` (card anatomy, action gating, `statusTransitions()`
  exclusions, merge-target rules).
- Design prototype: `lunar-v2-ui` `OrderDetail.vue`, `FulfilmentCard.vue`,
  `FulfilmentSplitDialog.vue`, `FulfilmentMergeDialog.vue`,
  `FulfilmentDispatchDialog.vue`.

## Implementation plan

Work lands on the existing `feat/panel-orders-section` branch (unmerged), so the
Orders section arrives fulfilment-centric from the start.

- [x] Slice 1 — Server: show-props reshape (card payload, transitions, `otherLines`,
      carriers-with-services, holdReasons/locations), new fulfilment routes +
      controller methods + requests, `fulfilments.store` removal, Pest coverage.
- [x] Slice 2 — Frontend: `FulfilmentCard` + `FulfilmentLineRow`, Show.vue
      restructure (flat table and create button removed, Other items section),
      badge/category tones, vitest.
- [x] Slice 3 — Dialogs and menus: ship (repeatable tracking) / split / merge /
      hold / tracking / location / shared confirm dialogs, update-status dropdown,
      ellipsis menu, translations across 16 locales, remaining tests.

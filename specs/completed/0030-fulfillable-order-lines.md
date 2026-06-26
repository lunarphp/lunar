# 0030 — Fulfillable order lines: decouple fulfilment from the line `type` string

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-06-12
- TODO item: robustness follow-up surfaced reviewing spec 0022

## Problem

The fulfilment system decides which order lines are shippable by comparing the line's `type` column against the literal string `'physical'` — and that string is convention, not contract:

- `Purchasable::getType()` is an untyped contract method whose docblock says *"i.e. physical,digital,shipping"* — an example list, not a constraint. `Purchasable` is a public extension point implemented downstream for gift cards, bundles, subscriptions, tickets.
- `ProductVariant::getType()` derives the value from its `shippable` boolean; the `CreateOrderLines` pipeline copies whatever string comes back verbatim into `order_lines.type` (plain string column, no enum, no validation).
- `Order::physicalLines()` is `whereType('physical')`, and spec 0022 made it load-bearing: `EnsureInitialFulfilment` (which lines the initial parcel covers), `CreateFulfilment::canRun()` (whether the admin offers fulfilment), and `ResolveFulfilmentStatus` (both sides of the rollup) all hang off it.

The concrete failure: a custom purchasable returning `'giftcard'` with `isShippable() === true` is invisible to fulfilments — no initial parcel covers it, the admin cannot fulfil it, and an order containing only such lines has no `'physical'` lines, so `ResolveFulfilmentStatus` short-circuits to **`Fulfilled`** despite physical goods needing to be posted.

Two adjacent wrinkles:

- **Two parallel signals that can disagree.** The cart decides shippability via `Purchasable::isShippable()`; fulfilment decides via `getType() === 'physical'`. `ProductVariant` keeps them aligned; nothing forces a custom purchasable to.
- **`FulfilmentQuantity` doesn't filter by type**, so a fulfilment can be created (via the API) covering a digital or shipping line. It persists but the rollup ignores it — a `Pending` parcel can sit forever on an order reading `Fulfilled`.

## Proposal

Fulfilment's real semantic is "needs shipping", and the contract already carries it: `Purchasable::isShippable()`. Stamp that answer onto the order line at creation and key the fulfilment system off it. `type` reverts to being an open-set categorisation/display value.

### Column

`order_lines` baseline migration gains:

```
requires_shipping   boolean, default false, indexed   (after `type`)
```

Cast `'requires_shipping' => 'boolean'` on `OrderLine`, `@property bool $requires_shipping`.

### Stamping

- `Pipelines/Order/Creation/CreateOrderLines` sets `'requires_shipping' => $cartLine->purchasable->isShippable()`.
- `Pipelines/Order/Creation/CreateShippingLine` sets `'requires_shipping' => false` explicitly (`ShippingOption::isShippable()` is already `false`; the explicit value keeps the fill self-documenting).
- `OrderLineFactory` derives the default from the line type — `fn (array $attributes) => ($attributes['type'] ?? 'physical') === 'physical'` — so factory-built physical lines are fulfillable and digital ones are not, matching production stamping without every test setting it.

### Consumption

- `Order::fulfillableLines(): HasMany` — `lines()->where('requires_shipping', true)` — added to the model and `Models\Contracts\Order`.
- The fulfilment call sites switch from `physicalLines()` to `fulfillableLines()`: `EnsureInitialFulfilment`, `CreateFulfilment::canRun()`, `ResolveFulfilmentStatus` (denominator and the numerator's line filter).
- `physicalLines()` / `digitalLines()` remain as type-based display helpers; nothing in the fulfilment domain references `type` any more.

### Validation

`Validation/Fulfilment/FulfilmentQuantity` rejects covering a line with `requires_shipping === false` (`lunar::exceptions.fulfilment_line_not_fulfillable`), closing the orphaned-parcel wrinkle: every fulfilment line is now a line the rollup counts.

## Alternatives considered

- **An `OrderLineType` enum with a typed `getType()`.** Rejected: it either closes the open set (hostile to custom purchasables, contradicting the contract's "i.e." framing) or stays open and fixes nothing — the fulfilment system would still be matching one privileged string.
- **Deriving fulfillability at read time from `purchasable->isShippable()`.** Rejected: order lines must stay truthful snapshots (the purchasable may change or be deleted after the order is placed), and the rollup queries need an indexable column, same reasoning as the stored `payment_status` / `fulfilment_status`.
- **Do nothing.** Rejected: spec 0022 turned a display grouping into the input of a derived, customer-notifying status.

## Migration impact

- **Database**: `requires_shipping` added to the `order_lines` baseline (v2 pre-release in-place edit).
- **Breaking changes**: `Models\Contracts\Order` gains `fulfillableLines()` — consumers implementing the contract from scratch must add it (models extending the shipped base inherit it). Behavioural: fulfilments can no longer be created against non-shippable lines (previously silently ignored by the rollup).
- **Upgrade path (stage 3, `packages/upgrade`)**: backfill `requires_shipping = (type = 'physical')` for v1 orders — v1 had no other signal.
- **Translations**: one new key, `exceptions.fulfilment_line_not_fulfillable`, across all 16 locales.
- **Filament / admin**: none — the admin already flows through the core actions and `CreateFulfilment::canRun()`.

## Open questions

None.

## References

- [[0022-order-fulfilments]] — §C (`ResolveFulfilmentStatus` over "physical lines") and §G; this spec re-keys those to `fulfillableLines()`.
- [[0029-entry-point-conventions]] — the review conversation that surfaced this.
- `Contracts/Purchasable.php` — `getType()` (open set, display) vs `isShippable()` (the fulfilment semantic).

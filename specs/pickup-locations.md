# Pickup locations: say which locations customers can collect from

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-11-24
- TODO item: Pickup locations — a location says whether customers can collect from it, and pickup is only offered when one can (spec pickup-locations)

## Problem

[[0083-cart-line-fulfilment-method]] lets a customer collect a line from a location and lets a variant be collection-only. Every location can be a pickup point, though, and nothing records which ones actually have a counter:

- `ResolveCartFulfilment` falls back to `Location::getDefault()` for the pickup location, and `ChangeFulfilmentMethod` does the same. A store whose default location is a warehouse with no counter sends pickup customers there.
- `Cart::setPickupLocation()` accepts any location, and the table-rate `Pickup` driver's location select and the panel and Filament change-method actions list every location.
- `Pickup::supports()` only asks whether the purchasable is shippable, so the variant form always lists Pickup. A store with no counter anywhere can still make an item collection-only that no customer can collect.

## Proposal

### Schema

`locations` gains `accepts_pickup`, a boolean defaulting to `false`, folded into the baseline `create_locations_table` migration while v2 is in alpha. `Location` casts it, and gains a `scopeAcceptsPickup()` scope and a static `defaultPickup(): ?Location`: the default location when it accepts pickup, otherwise the first location that does by name, otherwise null. It is memoised per request the way `getDefault()` is.

So that pickup keeps working where it works today, every existing install marks its default location as accepting pickup (see Migration impact). New locations start with it off.

### Pickup is only supported when a location accepts it

`Drivers\FulfilmentMethods\Pickup::supports()` becomes `$purchasable->isShippable() && Location::defaultPickup() !== null`, with the location lookup injected rather than called statically on the model. Everything that reads `supporting()` then follows from one rule: the variant form doesn't list Pickup, `CartLineFulfilmentMethod` rejects `pickup`, and `ResolveCartFulfilment` skips it as a cart default.

A collection-only variant (`fulfilment_methods = ['pickup']`) has no supporting method once no location accepts pickup. Today such a line resolves to null and `Shipping::claim()` would ship it. `CartLineFulfilmentMethod` gains a second rule: a purchasable that restricts its methods and has none left can't be added, with `exceptions.carts.fulfilment_method_none` (":identifier can't be fulfilled right now").

### Choosing the location

- `ResolveCartFulfilment` and `ChangeFulfilmentMethod` fall back to `Location::defaultPickup()` instead of `Location::getDefault()`.
- `SetCartPickupLocation` runs a new step list, `StepList::SetPickupLocationValidation`, with default `Validation\Cart\CartPickupLocation`: the location is null or accepts pickup. It fails with `exceptions.carts.pickup_location_unavailable`.
- `ChangeFulfilmentMethod` rejects a target pickup location that doesn't accept pickup, with the same message.
- The table-rate `Pickup` driver offers no option when its `pickup_location_id` no longer accepts pickup, or when it has none and `defaultPickup()` is null.
- An order already placed keeps its `pickup_location_id` and its fulfilments' locations. Turning `accepts_pickup` off doesn't move them.

### Admin

- The panel and Filament location forms get a toggle, "Customers can collect from here", and the location index shows it.
- The table-rate pickup method's location select, and the change-to-pickup location select on panel and Filament fulfilment cards, list only locations that accept pickup.
- The variant form needs no change of its own: Pickup drops out of the checklist through `supporting()`. When a variant is already restricted to `pickup` and pickup is no longer available, the form shows a warning instead of an empty checklist (open question 2).

## Alternatives considered

- **Hide Pickup in the forms only.** Leaves the cart, the resolver and the change-method action free to pick a location that has no counter. The rule belongs where `supporting()` reads it.
- **A `pickup` flag on the shipping method only.** The table-rate method already carries a location, but carts can be all-pickup with no shipping option at all ([[0083-cart-line-fulfilment-method]] section G), and staff change fulfilments without one.
- **Default `accepts_pickup` to `true`.** Matches today's behaviour for every location, but a store adding a warehouse would offer it for collection until someone noticed. Marking only the existing default location keeps today's behaviour without that trap.
- **Per-location pickup hours or instructions.** Useful, but storefront presentation; `meta` on the location covers it until a spec needs a typed field.

## Migration impact

- **Database:** `locations.accepts_pickup` in the baseline migration. Row under "Next release" in the alpha upgrade guide: add the column and set it to `true` for the default location. A guarded data step in `packages/upgrade` does the same for v1 stores, whose pickups were fulfilled from the default location.
- **Contract changes (in place while v2 is in alpha):** `Pickup::supports()` also depends on a location accepting pickup; `ResolveCartFulfilment` and `ChangeFulfilmentMethod` fall back to `Location::defaultPickup()`; `SetCartPickupLocation` validates.
- **New surface:** `Location::$accepts_pickup`, `scopeAcceptsPickup()`, `defaultPickup()`; `StepList::SetPickupLocationValidation` and `Validation\Cart\CartPickupLocation`.
- **Translations (16 locales):** `exceptions.carts.pickup_location_unavailable` and `exceptions.carts.fulfilment_method_none` in `core`; the location toggle, its index column and the variant warning in `panel` and `admin`.
- **Docs:** the locations and fulfilments references, and the storefront guidance on listing pickup locations.

## Open questions

1. **Choosing among several pickup locations.** `defaultPickup()` picks the default, then the first by name. Should a store be able to order them, or is the storefront expected to set the location explicitly once there is more than one?
2. **A collection-only variant when pickup is unavailable.** Warn in the form and refuse it at add-to-cart (proposed), or also hide the product from listings?

## References

- [[0083-cart-line-fulfilment-method]]: the pickup location on the cart and order, `supporting()`, and the variant fulfilment checklist. This spec builds on it (#2865).
- [[0038-inventory-fundamentals]] and the location-scoped availability idea in `TODO.md`: stock routing per location, which this spec doesn't attempt.
- `Actions/Fulfilment/ResolveCartFulfilment.php`, `Actions/Fulfilment/ChangeFulfilmentMethod.php`, `Actions/Carts/SetCartPickupLocation.php`, `Drivers/FulfilmentMethods/Pickup.php`, `table-rate-shipping/src/Drivers/ShippingMethods/Pickup.php`.

## Implementation plan

- [ ] Slice 1 — Core: the column, cast, scope and `defaultPickup()`; `Pickup::supports()`; the resolver and change-method fallbacks; the pickup-location validator and the no-method rule in `CartLineFulfilmentMethod`; exception messages in 16 locales; the alpha guide row and the v1 upgrade step. Tests.
- [ ] Slice 2 — Table-rate shipping: the `Pickup` driver and its location select. Tests.
- [ ] Slice 3 — Panel and Filament: the location toggle and index column, the filtered location selects, the variant warning, translations. Docs.

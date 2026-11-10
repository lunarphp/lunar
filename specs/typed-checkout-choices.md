# Typed checkout choices: pickup location and payment terms

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-11-10
- TODO item: Typed checkout choices — record the customer's pickup location and the order's payment terms as typed order fields instead of meta (spec typed-checkout-choices)

## Problem

Two choices a customer makes at checkout are recorded only in untyped meta, and core now makes decisions on one of them.

**Pickup.**
- `ShippingOption::$pickup` is stamped onto the order's shipping line as `meta['pickup']` by `CreateShippingLine`. `Order::isPickup()` reads it, and `Pickup::claim()` uses it to decide which lines become a pickup fulfilment.
- Meta has no type. A stored `"false"` counted as a pickup until `isPickup()` started comparing with `=== true`, and every new reader has to remember to do the same.
- Core doesn't record *where* the customer picks up. `CreateFulfilment` gives every fulfilment, pickup included, the default location. Checkouts write their own `meta['pickup_point']` onto the shipping line in a shape core doesn't define, and the panel has to clean it defensively (#2749).
- A `Location` has a name, a handle and meta, but no address, so even a correct location can't tell the customer where to go.

**Payment terms.**
- An order invoiced on account sits in `Pending` by design, but nothing records that it is meant to. The panel infers it from `meta['payment_method'] === 'on-account'`, a handle that nothing in core writes (#2749).
- Elsewhere the order still reads as an ordinary unpaid "Pending" order, for example in the order index and notifications.

Both are facts about the order as placed, chosen at checkout, that core and the admin need to rely on. Settling them before beta avoids a breaking change and a data migration later.

## Proposal

### The choice and its execution

Two facts are kept apart:

- **What the customer chose** belongs on the order. It exists before any fulfilment does, because fulfilment creation reads it.
- **How the goods are handed over** belongs on the fulfilment, which already records a `method` and a `location_id`.

Displays prefer the fulfilment, and fall back to the order's choice before a fulfilment exists.

### In-store pickup

- `ShippingOption` gains `public ?Location $pickupLocation = null`. An option with a pickup location is a pickup; `$pickup` becomes derived (`$pickupLocation !== null`) and is deprecated.
- `orders.pickup_location_id`: a nullable foreign key to `locations`, set by `CreateShippingLine` from the chosen option. Null means the order is shipped or has nothing to ship.
- `Order::pickupLocation()` (`BelongsTo`) and `Order::isPickup()`, which becomes `pickup_location_id !== null`.
- `Pickup::claim()` reads `isPickup()`, and the fulfilment it creates is assigned to the order's pickup location rather than the default location.
- `Location` gains an address, so the pickup location can be shown to the customer and in the admin: either address columns on `locations` or a related address, decided below.
- `meta['pickup']` and `meta['pickup_point']` stop being written. The panel shows the pickup fulfilment's location, falling back to the order's pickup location.

### Delivery to a pickup point

A locker or a carrier's parcel shop is not one of the store's locations, and the goods reach it by carrier with tracking. It stays a **shipping** fulfilment, with the point's address as the shipping address. If a store needs to show that the address is a point rather than the customer's home, the shipping address carries that (see open questions). This spec does not model points as locations.

### Payment terms

- An enum `Lunar\Core\Enums\PaymentTerms`: `Checkout` (paid at checkout, the default), `OnAccount` (invoiced and paid later), and `OnPickup` (paid when collected).
- `orders.payment_terms`: a string column cast to the enum, defaulting to `checkout`. Set at order creation, either by the payment driver or by the checkout through the cart (for example `$cart->setPaymentTerms(PaymentTerms::OnAccount)`).
- `Order::isAwaitingPaymentByTerms()`: true when the terms are not `Checkout` and the payment status is still `Pending`. Core, the panel and Filament use it, instead of each matching a handle.
- The payment status label for such an order reads "On account" or "Pay on pickup" wherever the status is shown, through one method on the order rather than an override in one controller.
- The `payment-received` notification and the settlement banner already behave correctly for an order with nothing captured. They need no change, but their docs state how terms affect them.

## Alternatives considered

- **Keep the meta flags and document their shape.** Cheapest, but leaves core's fulfilment routing on untyped data and every store inventing its own pickup point shape.
- **Pickup only on the fulfilment.** The fulfilment is created from the choice, so the choice has to exist first, on the order.
- **Pickup points as `Location` records.** Locations are where stock is held and fulfilled from. A parcel shop isn't, and modelling it as one would let stock allocation and fulfilment location pickers offer it.
- **Put payment terms in the payment intent capabilities spec (0077).** That spec covers gateway intents before an order exists. Terms are about what payment an order expects at all, with no gateway involved, so they sit better with the other checkout choices.

## Migration impact

- Database: `orders.pickup_location_id` and `orders.payment_terms`, plus the location address. While v2 is in alpha these fold into the existing create-table migrations.
- Upgrade from v1: v1's `ShippingOption::$collect` was never stored on the order, so v1 pickup orders can't be identified and keep a null pickup location. `payment_terms` defaults to `checkout`. The upgrade guide says so.
- Public contract: `ShippingOption` gains `pickupLocation`, and `$pickup` is deprecated. `Order::isPickup()` keeps its signature. Rector can rewrite `pickup: true` where a location is obvious, but most call sites need the store to pick the location.
- Translations: the payment terms labels in all 16 locales.
- Admin: the panel and Filament read the typed fields. #2749's meta reads are replaced.

## Open questions

- **Location address:** columns on `locations`, or a morph to the existing address model? Columns are simpler; a related address reuses formatting and validation.
- **Changing a fulfilment's method:** core has no action to turn a pickup fulfilment into a shipping one (the customer can't come, so staff ship it). Is that needed, and does it belong here?
- **Marking a shipping address as a pickup point:** a typed field on the address, or out of scope?
- **Who sets payment terms:** the driver, the checkout, or both, and which wins?
- **`OnPickup` and the `paid` rollup:** should an order paid in store at pickup record a manual capture, so the ledger stays the single source of payment status?

## References

- #2749: panel order view for collection and on-account orders, where the meta reads were introduced.
- #2717: the rename of the collection fulfilment method to pickup.
- [[0077-payment-intent-capabilities]]
- `packages/core/src/Pipelines/Order/Creation/CreateShippingLine.php`, `packages/core/src/Drivers/FulfilmentMethods/Pickup.php`, `packages/core/src/Actions/Fulfilment/CreateFulfilment.php`.

## Implementation plan

- [ ] Slice 1 — Location address.
- [ ] Slice 2 — `ShippingOption::$pickupLocation`, `orders.pickup_location_id`, `Order::pickupLocation()`, `isPickup()` on the column, and pickup fulfilments created at that location.
- [ ] Slice 3 — `PaymentTerms`, `orders.payment_terms`, `isAwaitingPaymentByTerms()` and the label.
- [ ] Slice 4 — panel and Filament read the typed fields from the fulfilment and the order; docs and upgrade guide.
